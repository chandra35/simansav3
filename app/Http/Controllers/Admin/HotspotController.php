<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HotspotRadiusNas;
use App\Models\HotspotRadiusProfile;
use App\Models\HotspotUser;
use App\Models\Siswa;
use App\Models\Gtk;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HotspotController extends Controller
{
    public function index()
    {
        $stats = $this->getStats(false);
        $radiusConnected = null;
        $profiles = HotspotRadiusProfile::query()->orderBy('role')->orderBy('priority')->orderBy('name')->get();
        $nasList = HotspotRadiusNas::query()->orderBy('name')->get();
        $serverInfo = $this->getRadiusServerInfo();

        return view('admin.hotspot.index', compact('stats', 'radiusConnected', 'profiles', 'nasList', 'serverInfo'));
    }

    public function data(Request $request)
    {
        $query = HotspotUser::with(['user', 'user.siswa.kelasAktif', 'radiusProfile'])
            ->withTrashed($request->boolean('show_deleted'));

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('sync_status')) {
            $query->where('sync_status', $request->sync_status);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool)$request->is_active);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        // Filter kelas (hanya berlaku saat role = siswa)
        if ($request->input('role') === 'siswa' &&
            ($request->filled('tingkat') || $request->filled('kelas_id'))) {
            $query->whereHas('user.siswa.kelasAktif', function ($q) use ($request) {
                if ($request->filled('tingkat')) {
                    $q->where('kelas.tingkat', $request->tingkat);
                }
                if ($request->filled('kelas_id')) {
                    $q->where('kelas.id', $request->kelas_id);
                }
            });
        }

        return datatables()->of($query)
            ->addColumn('kelas_info', function (HotspotUser $h) {
                if ($h->role !== 'siswa' || !$h->user || !$h->user->siswa) return '';
                $kelas = $h->user->siswa->kelasAktif->first();
                if (!$kelas) return '<span class="text-muted" style="font-size:.75rem">-</span>';
                return '<span class="badge badge-light border" style="font-size:.72rem">' . e($kelas->nama_kelas) . '</span>';
            })
            ->addColumn('status_badge', function (HotspotUser $h) {
                if ($h->deleted_at) {
                    return '<span class="badge badge-secondary"><i class="fas fa-trash mr-1"></i>Dihapus</span>';
                }
                if (!$h->is_active) {
                    return '<span class="badge badge-danger"><i class="fas fa-ban mr-1"></i>Nonaktif</span>';
                }
                if ($h->isExpired()) {
                    return '<span class="badge badge-warning"><i class="fas fa-clock mr-1"></i>Expired</span>';
                }
                return '<span class="badge badge-success"><i class="fas fa-check mr-1"></i>Aktif</span>';
            })
            ->addColumn('sync_badge', function (HotspotUser $h) {
                return match($h->sync_status) {
                    'synced'  => '<span class="badge badge-success"><i class="fas fa-sync mr-1"></i>Synced</span>',
                    'pending' => '<span class="badge badge-warning"><i class="fas fa-hourglass mr-1"></i>Pending</span>',
                    'error'   => '<span class="badge badge-danger" title="'.$h->sync_error.'"><i class="fas fa-exclamation-triangle mr-1"></i>Error</span>',
                    default   => '<span class="badge badge-secondary">'.$h->sync_status.'</span>',
                };
            })
            ->addColumn('role_badge', function (HotspotUser $h) {
                return match($h->role) {
                    'guru'  => '<span class="badge badge-primary"><i class="fas fa-chalkboard-teacher mr-1"></i>Guru</span>',
                    'siswa' => '<span class="badge badge-info"><i class="fas fa-user-graduate mr-1"></i>Siswa</span>',
                    'tamu'  => '<span class="badge badge-warning"><i class="fas fa-user mr-1"></i>Tamu</span>',
                    default => '<span class="badge badge-secondary">'.$h->role.'</span>',
                };
            })
            ->addColumn('profile_badge', function (HotspotUser $h) {
                $profile = $h->radiusProfile ?: HotspotRadiusProfile::defaultForRole($h->role);
                if (!$profile) {
                    return '<span class="badge badge-light border text-muted">Default role</span>';
                }

                $rate = $profile->rate_limit ? '<small class="d-block text-muted">'.e($profile->rate_limit).'</small>' : '';
                return '<span class="badge badge-primary">'.e($profile->name).'</span>'.$rate;
            })
            ->addColumn('actions', function (HotspotUser $h) {
                $btn = '';
                if (!$h->deleted_at) {
                    $btn .= '<button class="btn btn-xs btn-info btn-sync-single" data-id="'.$h->id.'" title="Sync ulang"><i class="fas fa-sync"></i></button> ';
                    if ($h->is_active) {
                        $btn .= '<button class="btn btn-xs btn-warning btn-toggle-active" data-id="'.$h->id.'" data-active="1" title="Nonaktifkan"><i class="fas fa-ban"></i></button> ';
                    } else {
                        $btn .= '<button class="btn btn-xs btn-success btn-toggle-active" data-id="'.$h->id.'" data-active="0" title="Aktifkan"><i class="fas fa-check"></i></button> ';
                    }
                    if ($h->role === 'tamu') {
                        $btn .= '<button class="btn btn-xs btn-secondary btn-edit-tamu" data-id="'.$h->id.'" data-username="'.$h->username.'" data-displayname="'.e($h->display_name).'" data-keterangan="'.e($h->keterangan).'" data-expired="'.($h->expired_at?->format('Y-m-d') ?? '').'" title="Edit"><i class="fas fa-edit"></i></button> ';
                        $btn .= '<button class="btn btn-xs btn-danger btn-delete" data-id="'.$h->id.'" title="Hapus"><i class="fas fa-trash"></i></button>';
                    }
                }
                return $btn;
            })
            ->rawColumns(['kelas_info', 'status_badge', 'sync_badge', 'role_badge', 'profile_badge', 'actions'])
            ->make(true);
    }

    public function sync(Request $request)
    {
        $role = $request->input('role', '');
        $force = $request->boolean('force');

        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $params = ['--force' => $force];
        if ($role) {
            $params['--role'] = $role;
        }

        Artisan::call('hotspot:sync', $params, $output);
        $result = $output->fetch();

        // Parse counts from output
        $created     = (int) (preg_match('/Created\s*:\s*(\d+)/i', $result, $m) ? $m[1] : 0);
        $updated     = (int) (preg_match('/Updated\s*:\s*(\d+)/i', $result, $m) ? $m[1] : 0);
        $deactivated = (int) (preg_match('/Deactivated\s*:\s*(\d+)/i', $result, $m) ? $m[1] : 0);
        $errors      = (int) (preg_match('/Errors\s*:\s*(\d+)/i', $result, $m) ? $m[1] : 0);

        return response()->json([
            'success'     => true,
            'message'     => 'Sync selesai.',
            'output'      => nl2br(htmlspecialchars($result)),
            'counts'      => compact('created', 'updated', 'deactivated', 'errors'),
            'stats'       => $this->getStats(false),
        ]);
    }

    public function stats()
    {
        return response()->json($this->getStats(false));
    }

    public function syncSingle(Request $request, HotspotUser $hotspot)
    {
        $hotspot->loadMissing('user.siswa');

        if (!$hotspot->isEligibleForRadius()) {
            $hotspot->rejectFromRadius('Akun tidak disync karena siswa/user tidak aktif.');

            return response()->json([
                'success' => false,
                'message' => 'Akun tidak eligible untuk RADIUS. Khusus siswa hanya status aktif yang boleh disync.',
            ], 422);
        }

        $user = $hotspot->user;
        if (!$user || empty($user->encrypted_password)) {
            return response()->json(['success' => false, 'message' => 'Password tidak ditemukan di Simansa.'], 422);
        }

        try {
            $plain = Crypt::decryptString($user->encrypted_password);
        } catch (\Exception) {
            return response()->json(['success' => false, 'message' => 'Gagal decrypt password.'], 422);
        }

        $ok = $hotspot->syncToRadius($plain);

        return response()->json([
            'success' => $ok,
            'message' => $ok ? 'Sync berhasil.' : 'Sync gagal: ' . $hotspot->sync_error,
        ]);
    }

    public function toggleActive(Request $request, HotspotUser $hotspot)
    {
        $hotspot->loadMissing('user.siswa');
        $willActivate = !$hotspot->is_active;

        if ($willActivate && !$hotspot->isEligibleForRadius()) {
            $hotspot->rejectFromRadius('Aktivasi ditolak karena siswa/user tidak aktif.');

            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa mengaktifkan akun ini. Untuk siswa, hanya status aktif yang boleh masuk RADIUS.',
            ], 422);
        }

        $hotspot->update(['is_active' => $willActivate]);

        // Sync status ke RADIUS
        if (!$hotspot->is_active) {
            DB::connection('mysql_radius')->table('radcheck')->updateOrInsert(
                ['username' => $hotspot->username, 'attribute' => 'Auth-Type'],
                ['op' => ':=', 'value' => 'Reject']
            );
        } else {
            DB::connection('mysql_radius')->table('radcheck')
                ->where('username', $hotspot->username)
                ->where('attribute', 'Auth-Type')
                ->delete();
        }

        $hotspot->update(['sync_status' => 'synced', 'last_synced_at' => now()]);

        return response()->json([
            'success' => true,
            'is_active' => $hotspot->is_active,
            'message' => $hotspot->is_active ? 'Akun diaktifkan.' : 'Akun dinonaktifkan.',
        ]);
    }

    public function storeTamu(Request $request)
    {
        $request->validate([
            'display_name' => 'required|string|max:150',
            'keterangan' => 'nullable|string|max:255',
            'password' => 'required|string|min:4|max:64',
            'expired_at' => 'nullable|date|after:now',
        ]);

        $username = 'tamu.' . Str::slug($request->display_name) . '.' . substr(uniqid(), -4);

        $hotspot = HotspotUser::create([
            'username' => $username,
            'role' => 'tamu',
            'display_name' => $request->display_name,
            'keterangan' => $request->keterangan,
            'is_active' => true,
            'expired_at' => $request->expired_at,
            'sync_status' => 'pending',
        ]);

        $ok = $hotspot->syncToRadius($request->password);

        return response()->json([
            'success' => $ok,
            'message' => $ok ? 'Akun tamu berhasil dibuat.' : 'Dibuat tapi sync gagal.',
            'username' => $username,
            'password' => $request->password,
        ]);
    }

    public function updateTamu(Request $request, HotspotUser $hotspot)
    {
        if ($hotspot->role !== 'tamu') {
            return response()->json(['success' => false, 'message' => 'Bukan akun tamu.'], 403);
        }

        $request->validate([
            'display_name' => 'required|string|max:150',
            'keterangan' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:4|max:64',
            'expired_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $hotspot->update([
            'display_name' => $request->display_name,
            'keterangan' => $request->keterangan,
            'expired_at' => $request->expired_at,
            'is_active' => $request->boolean('is_active', $hotspot->is_active),
        ]);

        if ($request->filled('password')) {
            $hotspot->syncToRadius($request->password);
        } else {
            // Sync status saja (aktif/nonaktif)
            $user = $hotspot->user;
            if ($user) {
                try {
                    $plain = Crypt::decryptString($user->encrypted_password);
                    $hotspot->syncToRadius($plain);
                } catch (\Exception) {}
            }
        }

        return response()->json(['success' => true, 'message' => 'Akun tamu diupdate.']);
    }

    public function destroyTamu(HotspotUser $hotspot)
    {
        if ($hotspot->role !== 'tamu') {
            return response()->json(['success' => false, 'message' => 'Bukan akun tamu.'], 403);
        }

        $hotspot->removeFromRadius();
        $hotspot->delete();

        return response()->json(['success' => true, 'message' => 'Akun tamu dihapus.']);
    }

    public function radiusStatus()
    {
        try {
            $db = DB::connection('mysql_radius');
            $db->getPdo();

            $counts = [
                'radcheck' => $db->table('radcheck')->count(),
                'radusergroup' => $db->table('radusergroup')->count(),
                'radgroupreply' => $this->radiusTableExists($db, 'radgroupreply') ? $db->table('radgroupreply')->count() : null,
                'nas' => $this->radiusTableExists($db, 'nas') ? $db->table('nas')->count() : null,
                'radacct_active' => $db->table('radacct')->whereNull('acctstoptime')->count(),
                'radpostauth_today' => $db->table('radpostauth')
                    ->whereDate('authdate', today())
                    ->count(),
            ];

            $recentAuth = $db->table('radpostauth')
                ->orderByDesc('authdate')
                ->limit(10)
                ->get();

            return response()->json([
                'connected' => true,
                'counts' => $counts,
                'recent_auth' => $recentAuth,
                'server' => $this->getRadiusServerInfo(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['connected' => false, 'error' => $e->getMessage()]);
        }
    }

    public function profiles()
    {
        $profiles = HotspotRadiusProfile::query()
            ->withCount('users')
            ->orderBy('role')
            ->orderBy('priority')
            ->orderBy('name')
            ->get()
            ->map(fn (HotspotRadiusProfile $profile) => [
                'id' => $profile->id,
                'code' => $profile->code,
                'name' => $profile->name,
                'role' => $profile->role,
                'rate_limit' => $profile->rate_limit,
                'session_timeout' => $profile->session_timeout,
                'idle_timeout' => $profile->idle_timeout,
                'simultaneous_use' => $profile->simultaneous_use,
                'framed_pool' => $profile->framed_pool,
                'address_list' => $profile->address_list,
                'priority' => $profile->priority,
                'description' => $profile->description,
                'is_default' => $profile->is_default,
                'is_active' => $profile->is_active,
                'sync_status' => $profile->sync_status,
                'sync_error' => $profile->sync_error,
                'users_count' => $profile->users_count,
            ]);

        return response()->json(['success' => true, 'profiles' => $profiles]);
    }

    public function storeProfile(Request $request)
    {
        $data = $this->validateProfile($request);

        if ($request->boolean('is_default') && !empty($data['role'])) {
            HotspotRadiusProfile::where('role', $data['role'])->update(['is_default' => false]);
        }

        $profile = HotspotRadiusProfile::create($data + ['sync_status' => 'pending']);
        $synced = $profile->syncToRadius();

        return response()->json([
            'success' => true,
            'message' => $synced ? 'Profile RADIUS berhasil dibuat dan disinkronkan.' : 'Profile dibuat, tetapi sync ke FreeRADIUS gagal: '.$profile->sync_error,
            'profile' => $profile,
        ]);
    }

    public function updateProfile(Request $request, HotspotRadiusProfile $profile)
    {
        $data = $this->validateProfile($request, $profile->id);

        if ($request->boolean('is_default') && !empty($data['role'])) {
            HotspotRadiusProfile::where('role', $data['role'])
                ->whereKeyNot($profile->id)
                ->update(['is_default' => false]);
        }

        $profile->update($data + ['sync_status' => 'pending']);
        $synced = $profile->syncToRadius();

        return response()->json([
            'success' => true,
            'message' => $synced ? 'Profile RADIUS berhasil diperbarui dan disinkronkan.' : 'Profile diperbarui, tetapi sync ke FreeRADIUS gagal: '.$profile->sync_error,
            'profile' => $profile->fresh(),
        ]);
    }

    public function syncProfile(HotspotRadiusProfile $profile)
    {
        $ok = $profile->syncToRadius();

        return response()->json([
            'success' => $ok,
            'message' => $ok ? 'Profile berhasil disinkronkan ke FreeRADIUS.' : 'Sync profile gagal: '.$profile->sync_error,
        ], $ok ? 200 : 422);
    }

    public function destroyProfile(HotspotRadiusProfile $profile)
    {
        if ($profile->users()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Profile masih dipakai akun hotspot. Pindahkan akun terlebih dahulu.',
            ], 422);
        }

        $profile->removeFromRadius();
        $profile->delete();

        return response()->json(['success' => true, 'message' => 'Profile berhasil dihapus.']);
    }

    public function assignProfile(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1|max:1000',
            'ids.*' => 'integer|exists:hotspot_users,id',
            'profile_id' => 'nullable|exists:hotspot_radius_profiles,id',
        ]);

        $profile = $request->profile_id ? HotspotRadiusProfile::find($request->profile_id) : null;
        $users = HotspotUser::whereIn('id', $request->ids)->get();
        $count = 0;

        foreach ($users as $hotspot) {
            $hotspot->update([
                'hotspot_radius_profile_id' => $profile?->id,
                'sync_status' => 'pending',
            ]);

            $plain = $this->resolveHotspotPassword($hotspot);
            if ($plain !== null) {
                $hotspot->syncToRadius($plain);
            }

            $count++;
        }

        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => "{$count} akun diperbarui ke profile ".($profile?->name ?? 'default role').'.',
        ]);
    }

    public function storeNas(Request $request)
    {
        $nas = HotspotRadiusNas::create($this->validateNas($request) + ['sync_status' => 'pending']);

        return response()->json([
            'success' => true,
            'message' => 'NAS/MikroTik berhasil disimpan. Gunakan tombol Sync untuk menulis ke FreeRADIUS.',
            'nas' => $nas,
        ]);
    }

    public function updateNas(Request $request, HotspotRadiusNas $nas)
    {
        $data = $this->validateNas($request, $nas->id);
        if (!$request->filled('secret')) {
            unset($data['secret']);
        }

        $nas->update($data + ['sync_status' => 'pending']);

        return response()->json([
            'success' => true,
            'message' => 'NAS/MikroTik berhasil diperbarui. Gunakan tombol Sync untuk menulis ke FreeRADIUS.',
            'nas' => $nas->fresh(),
        ]);
    }

    public function syncNas(HotspotRadiusNas $nas)
    {
        $result = $nas->syncToRadiusWithLog();
        $ok = $result['success'];

        return response()->json([
            'success' => $ok,
            'message' => $ok ? 'NAS berhasil disinkronkan ke FreeRADIUS.' : 'Sync NAS gagal: '.$nas->sync_error,
            'steps' => $result['steps'] ?? [],
        ], $ok ? 200 : 422);
    }

    public function destroyNas(HotspotRadiusNas $nas)
    {
        $nas->update(['is_active' => false]);
        $nas->syncToRadius();
        $nas->delete();

        return response()->json(['success' => true, 'message' => 'NAS/MikroTik berhasil dihapus.']);
    }

    public function onlinePage()
    {
        $radiusConnected = $this->checkRadiusConnection();
        return view('admin.hotspot.online', compact('radiusConnected'));
    }

    public function onlineUsers()
    {
        try {
            $sessions = DB::connection('mysql_radius')
                ->table('radacct')
                ->whereNull('acctstoptime')
                ->orderByDesc('acctstarttime')
                ->get();

            $usernames  = $sessions->pluck('username')->unique()->values();
            $hotspotMap = HotspotUser::whereIn('username', $usernames)
                ->with('user.siswa.kelasAktif')
                ->get()
                ->keyBy('username');

            $result = $sessions->map(function ($s) use ($hotspotMap) {
                $hs    = $hotspotMap->get($s->username);
                $kelas = null;
                if ($hs && $hs->role === 'siswa' && $hs->user?->siswa) {
                    $kelas = optional($hs->user->siswa->kelasAktif->first())->nama_kelas;
                }

                return [
                    'session_id'   => $s->radacctid,
                    'username'     => $s->username,
                    'display_name' => $hs?->display_name ?? $s->username,
                    'role'         => $hs?->role ?? 'unknown',
                    'kelas'        => $kelas,
                    'hotspot_id'   => $hs?->id,
                    'is_active'    => $hs?->is_active ?? true,
                    'framed_ip'    => $s->framedipaddress,
                    'mac'          => $s->callingstationid,
                    'nas_ip'       => $s->nasipaddress,
                    'started_at'   => $s->acctstarttime,
                    'session_time' => (int) ($s->acctsessiontime ?? 0),
                    'bytes_in'     => (int) ($s->acctinputoctets ?? 0),
                    'bytes_out'    => (int) ($s->acctoutputoctets ?? 0),
                ];
            });

            return response()->json([
                'success'  => true,
                'count'    => $result->count(),
                'sessions' => $result->values(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function filterOptions()
    {
        $kelas = \App\Models\Kelas::select('id', 'nama_kelas', 'tingkat')
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get()
            ->map(fn($k) => [
                'id'     => $k->id,
                'nama'   => $k->nama_kelas,
                'tingkat'=> $k->tingkat,
            ]);

        return response()->json([
            'tingkat' => [10, 11, 12],
            'kelas'   => $kelas,
        ]);
    }

    public function bulkToggle(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array|min:1|max:500',
            'ids.*'  => 'integer|exists:hotspot_users,id',
            'action' => 'required|in:aktif,nonaktif',
        ]);

        $isActive = $request->action === 'aktif';
        $users    = HotspotUser::whereIn('id', $request->ids)->get();
        $radius   = DB::connection('mysql_radius');
        $count    = 0;
        $skipped  = 0;

        foreach ($users as $hotspot) {
            $hotspot->loadMissing('user.siswa');

            if ($isActive && !$hotspot->isEligibleForRadius()) {
                $hotspot->rejectFromRadius('Bulk aktivasi ditolak karena siswa/user tidak aktif.');
                $skipped++;
                continue;
            }

            $hotspot->update(['is_active' => $isActive]);

            // Sync status ke RADIUS
            if ($isActive) {
                $radius->table('radcheck')
                    ->where('username', $hotspot->username)
                    ->where('attribute', 'Auth-Type')
                    ->delete();
            } else {
                $radius->table('radcheck')->updateOrInsert(
                    ['username' => $hotspot->username, 'attribute' => 'Auth-Type'],
                    ['op' => ':=', 'value' => 'Reject']
                );
            }

            $hotspot->update(['sync_status' => 'synced', 'last_synced_at' => now()]);
            $count++;
        }

        $label = $isActive ? 'diaktifkan' : 'dinonaktifkan';
        $extra = $skipped > 0 ? " {$skipped} akun dilewati karena tidak eligible." : '';

        return response()->json([
            'success' => true,
            'count'   => $count,
            'skipped' => $skipped,
            'message' => "{$count} akun berhasil {$label}.{$extra}",
            'stats'   => $this->getStats(false),
        ]);
    }

    private function getStats(bool $includeRadius = false): array
    {
        return [
            'total' => HotspotUser::count(),
            'guru' => HotspotUser::guru()->count(),
            'siswa' => HotspotUser::siswa()->count(),
            'tamu' => HotspotUser::tamu()->count(),
            'aktif' => HotspotUser::where('is_active', true)->count(),
            'nonaktif' => HotspotUser::where('is_active', false)->count(),
            'error_sync' => HotspotUser::where('sync_status', 'error')->count(),
            'pending_sync' => HotspotUser::where('sync_status', 'pending')->count(),
            'online' => $includeRadius ? $this->getOnlineCount() : null,
        ];
    }

    private function getOnlineCount(): int
    {
        try {
            return DB::connection('mysql_radius')
                ->table('radacct')
                ->whereNull('acctstoptime')
                ->count();
        } catch (\Exception) {
            return 0;
        }
    }

    private function checkRadiusConnection(): bool
    {
        try {
            DB::connection('mysql_radius')->getPdo();
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    private function validateProfile(Request $request, ?int $ignoreId = null): array
    {
        $unique = 'unique:hotspot_radius_profiles,code';
        if ($ignoreId) {
            $unique .= ','.$ignoreId;
        }

        return $request->validate([
            'code' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z0-9_.-]+$/', $unique],
            'name' => 'required|string|max:120',
            'role' => 'nullable|in:guru,siswa,tamu',
            'rate_limit' => 'nullable|string|max:80',
            'session_timeout' => 'nullable|integer|min:60|max:31536000',
            'idle_timeout' => 'nullable|integer|min:60|max:86400',
            'simultaneous_use' => 'nullable|integer|min:1|max:50',
            'framed_pool' => 'nullable|string|max:80',
            'address_list' => 'nullable|string|max:80',
            'priority' => 'nullable|integer|min:1|max:999',
            'description' => 'nullable|string|max:1000',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);
    }

    private function validateNas(Request $request, ?int $ignoreId = null): array
    {
        $unique = 'unique:hotspot_radius_nas,nasname';
        if ($ignoreId) {
            $unique .= ','.$ignoreId;
        }

        return $request->validate([
            'name' => 'required|string|max:120',
            'nasname' => ['required', 'string', 'max:120', $unique],
            'shortname' => 'nullable|string|max:60',
            'type' => 'required|string|max:40',
            'ports' => 'nullable|integer|min:0|max:65535',
            'secret' => $ignoreId ? 'nullable|string|max:255' : 'required|string|max:255',
            'server' => 'nullable|string|max:80',
            'community' => 'nullable|string|max:80',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);
    }

    private function resolveHotspotPassword(HotspotUser $hotspot): ?string
    {
        if ($hotspot->role === 'tamu') {
            return null;
        }

        $user = $hotspot->user;
        if (!$user) {
            return null;
        }

        if (!empty($user->encrypted_password)) {
            try {
                return Crypt::decryptString($user->encrypted_password);
            } catch (\Throwable) {
            }
        }

        return $hotspot->username;
    }

    private function getRadiusServerInfo(): array
    {
        $host = config('database.connections.mysql_radius.host');
        $dbPort = config('database.connections.mysql_radius.port');
        $database = config('database.connections.mysql_radius.database');

        return [
            'host' => $host,
            'database_port' => $dbPort,
            'database' => $database,
            'auth_port' => env('RADIUS_AUTH_PORT', 1812),
            'acct_port' => env('RADIUS_ACCT_PORT', 1813),
            'coa_port' => env('RADIUS_COA_PORT', 3799),
            'shared_secret_hint' => env('RADIUS_SHARED_SECRET') ? str_repeat('*', 12) : 'Set di MikroTik dan FreeRADIUS clients/nas',
        ];
    }

    private function radiusTableExists($db, string $table): bool
    {
        try {
            return $db->getSchemaBuilder()->hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }
}
