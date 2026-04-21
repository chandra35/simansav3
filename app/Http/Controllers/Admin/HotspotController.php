<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $stats = $this->getStats();
        $radiusConnected = $this->checkRadiusConnection();

        return view('admin.hotspot.index', compact('stats', 'radiusConnected'));
    }

    public function data(Request $request)
    {
        $query = HotspotUser::with(['user', 'user.siswa.kelasAktif'])
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
            ->rawColumns(['kelas_info', 'status_badge', 'sync_badge', 'role_badge', 'actions'])
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
            'stats'       => $this->getStats(),
        ]);
    }

    public function stats()
    {
        return response()->json($this->getStats());
    }

    public function syncSingle(Request $request, HotspotUser $hotspot)
    {
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
        $hotspot->update(['is_active' => !$hotspot->is_active]);

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
            ]);
        } catch (\Exception $e) {
            return response()->json(['connected' => false, 'error' => $e->getMessage()]);
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

        foreach ($users as $hotspot) {
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

        return response()->json([
            'success' => true,
            'count'   => $count,
            'message' => "{$count} akun berhasil {$label}.",
            'stats'   => $this->getStats(),
        ]);
    }

    private function getStats(): array
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
            'online' => $this->getOnlineCount(),
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
}
