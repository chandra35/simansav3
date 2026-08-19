<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HotspotRadiusNas;
use App\Models\HotspotRadiusProfile;
use App\Models\HotspotUser;
use App\Models\Siswa;
use App\Models\Gtk;
use App\Models\User;
use App\Services\RadiusDisconnectService;
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
        $profiles = HotspotRadiusProfile::query()->orderBy('role')->orderBy('priority')->orderBy('name')->get();

        return view('admin.hotspot.index', compact('stats', 'profiles'));
    }

    public function settingsPage()
    {
        $radiusConnected = $this->checkRadiusConnection();
        $profiles = HotspotRadiusProfile::query()->withCount('users')->orderBy('role')->orderBy('priority')->orderBy('name')->get();
        $nasList = HotspotRadiusNas::query()->orderBy('name')->get();
        $serverInfo = $this->getRadiusServerInfo();
        $radiusDashboardUrl = config('hotspot.radius_dashboard_url');

        return view('admin.hotspot.settings', compact('radiusConnected', 'profiles', 'nasList', 'serverInfo', 'radiusDashboardUrl'));
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
                if ($h->deleted_at) {
                    return '<span class="text-muted">&mdash;</span>';
                }

                $items = '<button type="button" class="dropdown-item btn-sync-single" data-id="'.$h->id.'"><i class="fas fa-sync text-info mr-2"></i>Sync ulang</button>';
                if ($h->is_active) {
                    $items .= '<button type="button" class="dropdown-item btn-toggle-active" data-id="'.$h->id.'" data-active="1"><i class="fas fa-ban text-warning mr-2"></i>Nonaktifkan</button>';
                } else {
                    $items .= '<button type="button" class="dropdown-item btn-toggle-active" data-id="'.$h->id.'" data-active="0"><i class="fas fa-check text-success mr-2"></i>Aktifkan</button>';
                }
                if ($h->role === 'tamu') {
                    $items .= '<div class="dropdown-divider"></div>';
                    $items .= '<button type="button" class="dropdown-item btn-edit-tamu" data-id="'.$h->id.'" data-username="'.$h->username.'" data-displayname="'.e($h->display_name).'" data-keterangan="'.e($h->keterangan).'" data-expired="'.($h->expired_at?->format('Y-m-d') ?? '').'"><i class="fas fa-edit text-secondary mr-2"></i>Edit akun tamu</button>';
                    $items .= '<button type="button" class="dropdown-item btn-delete text-danger" data-id="'.$h->id.'"><i class="fas fa-trash mr-2"></i>Hapus akun tamu</button>';
                }

                return '<div class="dropdown hs-row-actions">'
                    .'<button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">Aksi</button>'
                    .'<div class="dropdown-menu dropdown-menu-right">'.$items.'</div>'
                    .'</div>';
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

        $plain = $this->resolveHotspotPassword($hotspot);
        if ($plain === null) {
            return response()->json(['success' => false, 'message' => 'Password Hotspot tidak tersedia atau tidak memenuhi kebijakan.'], 422);
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

        $hotspot->update(array_merge(
            ['is_active' => $willActivate],
            $willActivate ? [
                'blocked_at' => null,
                'blocked_by' => null,
                'block_reason' => null,
            ] : []
        ));

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
            'password_secret' => $request->password,
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
            $hotspot->update(['password_secret' => $request->password]);
            $hotspot->syncToRadius($request->password);
        } else {
            $hotspot->syncToRadius($hotspot->password_secret ?: '__DISABLED__');
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
                'mikrotik_group' => $profile->mikrotik_group,
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

    public function syncAllProfiles()
    {
        $profiles = HotspotRadiusProfile::query()->where('is_active', true)->get();
        $synced = 0;
        $failed = 0;

        foreach ($profiles as $profile) {
            $profile->syncToRadius() ? $synced++ : $failed++;
        }

        return response()->json([
            'success' => $failed === 0,
            'message' => "{$synced} profile tersinkron, {$failed} gagal.",
            'synced' => $synced,
            'failed' => $failed,
        ], $failed === 0 ? 200 : 422);
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

    public function authLogsPage()
    {
        $radiusConnected = $this->checkRadiusConnection();

        return view('admin.hotspot.auth-logs', compact('radiusConnected'));
    }

    public function profilesPage()
    {
        $radiusConnected = $this->checkRadiusConnection();
        $profiles = HotspotRadiusProfile::query()
            ->withCount('users')
            ->orderBy('role')
            ->orderBy('priority')
            ->orderBy('name')
            ->get();
        $radiusState = $this->radiusProfileState($profiles);

        return view('admin.hotspot.profiles', compact(
            'radiusConnected',
            'profiles',
            'radiusState'
        ));
    }

    public function onlineUsers()
    {
        try {
            $sessions = DB::connection('mysql_radius')
                ->table('radacct')
                ->whereNull('acctstoptime')
                ->orderByDesc('acctstarttime')
                ->get();
            $recentRaw = DB::connection('mysql_radius')->table('radacct')
                ->whereNotNull('acctstoptime')
                ->orderByDesc('acctstoptime')
                ->limit(15)
                ->get();

            $usernames = $sessions->pluck('username')
                ->merge($recentRaw->pluck('username'))
                ->unique()
                ->values();
            $hotspotMap = HotspotUser::whereIn('username', $usernames)
                ->with(['radiusProfile', 'user.siswa.kelasAktif', 'user.gtk'])
                ->get()
                ->keyBy('username');
            $defaultProfiles = HotspotRadiusProfile::query()
                ->where('is_active', true)
                ->where('is_default', true)
                ->get()
                ->keyBy('role');

            $result = $sessions->map(function ($s) use ($hotspotMap, $defaultProfiles) {
                $hs = $hotspotMap->get($s->username);
                $identity = $this->hotspotIdentity($hs);
                $elapsed = $s->acctstarttime ? now()->diffInSeconds($s->acctstarttime) : 0;
                $download = (int) ($s->acctinputoctets ?? 0);
                $upload = (int) ($s->acctoutputoctets ?? 0);

                return [
                    'session_id'   => $s->radacctid,
                    'acct_session_id' => $s->acctsessionid,
                    'username'     => $s->username,
                    'display_name' => $hs?->display_name ?? $s->username,
                    'role'         => $hs?->role ?? 'unknown',
                    'kelas'        => $identity['kelas'],
                    'kelas_id'     => $identity['kelas_id'],
                    'kelas_url'    => $identity['kelas_url'],
                    'photo_url'    => $identity['photo_url'],
                    'detail_url'   => $identity['detail_url'],
                    'identity'     => $identity['identity'],
                    'profile'      => $hs?->radiusProfile?->name
                        ?? $defaultProfiles->get($hs?->role)?->name,
                    'queue_name'   => '<hotspot-'.$s->username.'>',
                    'hotspot_id'   => $hs?->id,
                    'is_active'    => $hs?->is_active ?? true,
                    'framed_ip'    => $s->framedipaddress,
                    'mac'          => $s->callingstationid,
                    'nas_ip'       => $s->nasipaddress,
                    'nas_port'     => $s->nasportid ?? $s->nasport ?? null,
                    'terminate_cause' => $s->acctterminatecause,
                    'started_at'   => $s->acctstarttime,
                    'session_time' => max((int) ($s->acctsessiontime ?? 0), $elapsed),
                    'bytes_in'     => (int) ($s->acctinputoctets ?? 0),
                    'bytes_out'    => (int) ($s->acctoutputoctets ?? 0),
                    'bytes_download' => $download,
                    'bytes_upload' => $upload,
                ];
            });

            $today = now()->startOfDay();
            $accounting = DB::connection('mysql_radius')->table('radacct');
            $summary = [
                'sessions_today' => (clone $accounting)->where('acctstarttime', '>=', $today)->count(),
                'unique_today' => (clone $accounting)->where('acctstarttime', '>=', $today)->distinct('username')->count('username'),
                'download_today' => (int) (clone $accounting)->where('acctstarttime', '>=', $today)->sum('acctinputoctets'),
                'upload_today' => (int) (clone $accounting)->where('acctstarttime', '>=', $today)->sum('acctoutputoctets'),
            ];

            $blockedUsers = HotspotUser::query()
                ->whereNotNull('blocked_at')
                ->where('is_active', false)
                ->orderByDesc('blocked_at')
                ->limit(25)
                ->get(['id', 'username', 'display_name', 'role', 'blocked_at', 'block_reason'])
                ->map(fn (HotspotUser $user) => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'display_name' => $user->display_name,
                    'role' => $user->role,
                    'blocked_at' => $user->blocked_at?->toIso8601String(),
                    'block_reason' => $user->block_reason,
                ]);

            $recentSessions = $recentRaw->map(function ($session) use ($hotspotMap) {
                    $hotspot = $hotspotMap->get($session->username);

                    return [
                        'username' => $session->username,
                        'display_name' => $hotspot?->display_name ?? $session->username,
                        'stopped_at' => $session->acctstoptime,
                        'session_time' => (int) ($session->acctsessiontime ?? 0),
                        'terminate_cause' => $session->acctterminatecause ?: 'Tidak diketahui',
                        'framed_ip' => $session->framedipaddress,
                        'mac' => $session->callingstationid,
                    ];
                });

            return response()->json([
                'success'  => true,
                'count'    => $result->count(),
                'sessions' => $result->values(),
                'summary' => $summary,
                'recent_sessions' => $recentSessions,
                'blocked_users' => $blockedUsers,
                'server_time' => now()->toIso8601String(),
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

            $hotspot->update(array_merge(
                ['is_active' => $isActive],
                $isActive ? [
                    'blocked_at' => null,
                    'blocked_by' => null,
                    'block_reason' => null,
                ] : []
            ));

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

    public function disconnectSession(Request $request, RadiusDisconnectService $disconnect): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate(['session_id' => 'required|integer|min:1']);
        $session = DB::connection('mysql_radius')->table('radacct')
            ->where('radacctid', $validated['session_id'])
            ->whereNull('acctstoptime')
            ->first();

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Sesi sudah berakhir atau tidak ditemukan.'], 404);
        }

        $result = $this->disconnectRadiusSession($session, $disconnect);
        activity('hotspot')
            ->causedBy($request->user())
            ->withProperties([
                'username' => $session->username,
                'session_id' => $session->radacctid,
                'acct_session_id' => $session->acctsessionid,
                'framed_ip' => $session->framedipaddress,
                'mac' => $session->callingstationid,
                'nas_ip' => $session->nasipaddress,
                'success' => $result['success'],
                'reauthentication_required' => $result['reauthentication_required'] ?? false,
                'steps' => $result['steps'] ?? [],
            ])
            ->log('Admin meminta pemutusan sesi Hotspot');

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function blockUser(Request $request, HotspotUser $hotspot, RadiusDisconnectService $disconnect): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate(['reason' => 'nullable|string|max:255']);
        $hotspot->update([
            'is_active' => false,
            'blocked_at' => now(),
            'blocked_by' => $request->user()->getKey(),
            'block_reason' => $validated['reason'] ?? 'Diblokir dari Monitoring Hotspot',
            'sync_status' => 'synced',
            'last_synced_at' => now(),
        ]);
        DB::connection('mysql_radius')->table('radcheck')->updateOrInsert(
            ['username' => $hotspot->username, 'attribute' => 'Auth-Type'],
            ['op' => ':=', 'value' => 'Reject']
        );

        $sessions = DB::connection('mysql_radius')->table('radacct')
            ->where('username', $hotspot->username)
            ->whereNull('acctstoptime')
            ->get();
        $disconnected = 0;
        $failed = 0;
        foreach ($sessions as $session) {
            $this->disconnectRadiusSession($session, $disconnect)['success'] ? $disconnected++ : $failed++;
        }

        activity('hotspot')->performedOn($hotspot)->causedBy($request->user())
            ->withProperties(['disconnected' => $disconnected, 'disconnect_failed' => $failed, 'reason' => $hotspot->block_reason])
            ->log('Akun Hotspot diblokir');

        $message = "Akun diblokir. {$disconnected} sesi aktif diputus.";
        if ($failed > 0) {
            $message .= " {$failed} sesi belum mendapat konfirmasi dari MikroTik.";
        }

        return response()->json(['success' => $failed === 0, 'blocked' => true, 'message' => $message], $failed === 0 ? 200 : 422);
    }

    public function unblockUser(Request $request, HotspotUser $hotspot): \Illuminate\Http\JsonResponse
    {
        $hotspot->loadMissing('user.siswa');
        if (!$hotspot->isEligibleForRadius()) {
            return response()->json(['success' => false, 'message' => 'Akun tidak memenuhi syarat untuk diaktifkan kembali.'], 422);
        }

        $hotspot->update([
            'is_active' => true,
            'blocked_at' => null,
            'blocked_by' => null,
            'block_reason' => null,
            'sync_status' => 'synced',
            'last_synced_at' => now(),
        ]);
        DB::connection('mysql_radius')->table('radcheck')
            ->where('username', $hotspot->username)
            ->where('attribute', 'Auth-Type')
            ->delete();

        activity('hotspot')->performedOn($hotspot)->causedBy($request->user())->log('Blokir akun Hotspot dibuka');

        return response()->json(['success' => true, 'message' => 'Blokir dibuka. Pengguna dapat login kembali.']);
    }

    private function disconnectRadiusSession(object $session, RadiusDisconnectService $disconnect): array
    {
        $nas = HotspotRadiusNas::query()
            ->where('is_active', true)
            ->where('nasname', $session->nasipaddress)
            ->first();

        if (!$nas) {
            return ['success' => false, 'message' => 'NAS sesi ini belum terdaftar pada Setting Hotspot.'];
        }

        return $disconnect->disconnect($nas, $session);
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
            'mikrotik_group' => ['nullable', 'string', 'max:80', 'regex:/^[a-zA-Z0-9_.-]+$/'],
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
                $password = Crypt::decryptString($user->encrypted_password);

                return $hotspot->isSecurePassword($password) ? $password : null;
            } catch (\Throwable) {
            }
        }

        if ($hotspot->role === 'guru' && preg_match('/^\d{16}$/', $hotspot->username) === 1) {
            return $hotspot->username;
        }

        return null;
    }

    private function hotspotIdentity(?HotspotUser $hotspot): array
    {
        $fallbackName = $hotspot?->display_name ?: $hotspot?->username ?: 'User Hotspot';
        $fallbackPhoto = 'https://ui-avatars.com/api/?name='.urlencode($fallbackName).'&size=160&background=475569&color=ffffff';
        $payload = [
            'photo_url' => $fallbackPhoto,
            'detail_url' => null,
            'kelas' => null,
            'kelas_id' => null,
            'kelas_url' => null,
            'identity' => null,
        ];

        if (!$hotspot?->user) {
            return $payload;
        }

        if ($hotspot->role === 'siswa' && $hotspot->user->siswa) {
            $siswa = $hotspot->user->siswa;
            $kelas = $siswa->kelasAktif->first();

            return [
                'photo_url' => $siswa->foto_profile_url,
                'detail_url' => route('admin.siswa.show', $siswa),
                'kelas' => $kelas?->nama_kelas,
                'kelas_id' => $kelas?->id,
                'kelas_url' => $kelas ? route('admin.kelas.show', $kelas) : null,
                'identity' => [
                    'label' => 'NISN',
                    'value' => $siswa->nisn,
                    'secondary_label' => 'NIS Lokal',
                    'secondary_value' => $siswa->nis_lokal,
                    'status' => $siswa->status_siswa,
                ],
            ];
        }

        if ($hotspot->role === 'guru' && $hotspot->user->gtk) {
            $gtk = $hotspot->user->gtk;

            return [
                'photo_url' => $gtk->foto_profile_url,
                'detail_url' => route('admin.gtk.show', $gtk),
                'kelas' => null,
                'kelas_id' => null,
                'kelas_url' => null,
                'identity' => [
                    'label' => 'NIK',
                    'value' => $gtk->nik,
                    'secondary_label' => 'NIP/NUPTK',
                    'secondary_value' => $gtk->nip ?: $gtk->nuptk,
                    'status' => $gtk->status_kepegawaian ?: $gtk->jenis_ptk,
                ],
            ];
        }

        return $payload;
    }

    private function classifyAuthResult(object $log, ?HotspotUser $hotspot, $rejectSet, $disabledSet): array
    {
        if ($log->reply === 'Access-Accept') {
            return ['status' => 'success', 'reason' => 'Login berhasil dan akses diberikan.'];
        }

        if ($log->reply !== 'Access-Reject') {
            return ['status' => 'other', 'reason' => 'Respons RADIUS: '.$log->reply.'.'];
        }

        if (!$hotspot) {
            return ['status' => 'reject', 'reason' => 'Username tidak terdaftar di SIMANSA/Hotspot.'];
        }

        if ($disabledSet->has($log->username)) {
            return ['status' => 'reject', 'reason' => $hotspot?->role === 'guru'
                ? 'Akun GTK belum disinkronkan ulang atau password tidak tersedia di SIMANSA.'
                : 'Password Hotspot belum aman/tersedia; pengguna harus reset password SIMANSA.'];
        }

        if ($rejectSet->has($log->username) || !$hotspot->is_active) {
            return ['status' => 'reject', 'reason' => $hotspot->isExpired()
                ? 'Masa berlaku akun telah berakhir.'
                : 'Akun Hotspot sedang nonaktif atau tidak memenuhi syarat akses.'];
        }

        return ['status' => 'reject', 'reason' => 'Password salah atau autentikasi ditolak oleh kebijakan RADIUS.'];
    }

    private function radiusProfileState($profiles): array
    {
        try {
            $codes = $profiles->pluck('code');
            $replyRows = DB::connection('mysql_radius')->table('radgroupreply')
                ->whereIn('groupname', $codes)
                ->get();
            $checkRows = DB::connection('mysql_radius')->table('radgroupcheck')
                ->whereIn('groupname', $codes)
                ->get();

            return $profiles->mapWithKeys(function (HotspotRadiusProfile $profile) use ($replyRows, $checkRows) {
                $actual = $replyRows->where('groupname', $profile->code)
                    ->pluck('value', 'attribute')
                    ->merge($checkRows->where('groupname', $profile->code)->pluck('value', 'attribute'))
                    ->all();
                $expected = array_filter([
                    'Mikrotik-Rate-Limit' => $profile->rate_limit,
                    'Mikrotik-Group' => $profile->mikrotik_group,
                    'Session-Timeout' => $profile->session_timeout,
                    'Idle-Timeout' => $profile->idle_timeout,
                    'Framed-Pool' => $profile->framed_pool,
                    'Mikrotik-Address-List' => $profile->address_list,
                    'Simultaneous-Use' => $profile->simultaneous_use,
                ], fn ($value) => $value !== null && $value !== '');
                $normalizedActual = array_map('strval', $actual);
                $normalizedExpected = array_map('strval', $expected);
                $status = empty($actual) ? 'missing' : ($normalizedActual == $normalizedExpected ? 'synced' : 'drift');

                return [$profile->id => [
                    'status' => $status,
                    'actual' => $actual,
                    'expected' => $expected,
                ]];
            })->all();
        } catch (\Throwable $e) {
            Log::warning('[Hotspot] Unable to read RADIUS profile state', ['error' => $e->getMessage()]);

            return $profiles->mapWithKeys(fn ($profile) => [$profile->id => [
                'status' => 'offline',
                'actual' => [],
                'expected' => [],
            ]])->all();
        }
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

    public function authLogs(Request $request)
    {
        $validated = $request->validate([
            'result' => 'nullable|in:success,reject,other',
            'search' => 'nullable|string|max:100',
            'date' => 'nullable|date_format:Y-m-d',
            'per_page' => 'nullable|integer|min:10|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        try {
            $db = DB::connection('mysql_radius');
            $date = $validated['date'] ?? now()->toDateString();
            $perPage = (int) ($validated['per_page'] ?? 25);
            $query = $db->table('radpostauth')
                ->select(['id', 'username', 'reply', 'authdate', 'class'])
                ->whereDate('authdate', $date);

            if (!empty($validated['search'])) {
                $query->where('username', 'like', '%'.$validated['search'].'%');
            }

            if (($validated['result'] ?? null) === 'success') {
                $query->where('reply', 'Access-Accept');
            } elseif (($validated['result'] ?? null) === 'reject') {
                $query->where('reply', 'Access-Reject');
            } elseif (($validated['result'] ?? null) === 'other') {
                $query->whereNotIn('reply', ['Access-Accept', 'Access-Reject']);
            }

            $page = $query->orderByDesc('authdate')->paginate($perPage);
            $usernames = collect($page->items())->pluck('username')->unique()->values();
            $hotspotMap = HotspotUser::whereIn('username', $usernames)
                ->with(['user.siswa.kelasAktif', 'user.gtk'])
                ->get()
                ->keyBy('username');
            $rejectSet = $db->table('radcheck')
                ->whereIn('username', $usernames)
                ->where('attribute', 'Auth-Type')
                ->where('value', 'Reject')
                ->pluck('username')
                ->flip();
            $disabledSet = $db->table('radcheck')
                ->whereIn('username', $usernames)
                ->where('attribute', 'Cleartext-Password')
                ->where('value', '__DISABLED__')
                ->pluck('username')
                ->flip();

            $items = collect($page->items())->map(function ($log) use ($hotspotMap, $rejectSet, $disabledSet) {
                $hotspot = $hotspotMap->get($log->username);
                $classification = $this->classifyAuthResult($log, $hotspot, $rejectSet, $disabledSet);
                $identity = $this->hotspotIdentity($hotspot);

                return [
                    'id' => $log->id,
                    'username' => $log->username,
                    'display_name' => $hotspot?->display_name ?? $log->username,
                    'role' => $hotspot?->role ?? 'unknown',
                    'reply' => $log->reply,
                    'status' => $classification['status'],
                    'reason' => $classification['reason'],
                    'authdate' => $log->authdate,
                    'photo_url' => $identity['photo_url'],
                    'detail_url' => $identity['detail_url'],
                    'kelas' => $identity['kelas'],
                ];
            });

            $daily = $db->table('radpostauth')
                ->whereDate('authdate', $date)
                ->selectRaw("COUNT(*) total, SUM(reply = 'Access-Accept') accepted, SUM(reply = 'Access-Reject') rejected, SUM(reply NOT IN ('Access-Accept','Access-Reject')) other")
                ->first();

            return response()->json([
                'success' => true,
                'logs' => $items,
                'summary' => [
                    'total' => (int) ($daily->total ?? 0),
                    'accepted' => (int) ($daily->accepted ?? 0),
                    'rejected' => (int) ($daily->rejected ?? 0),
                    'other' => (int) ($daily->other ?? 0),
                ],
                'pagination' => [
                    'current_page' => $page->currentPage(),
                    'last_page' => $page->lastPage(),
                    'per_page' => $page->perPage(),
                    'total' => $page->total(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('[Hotspot] Failed to load authentication logs', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Log autentikasi tidak dapat dimuat.'], 500);
        }
    }
}
