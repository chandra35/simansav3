<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Siswa;
use App\Models\ActivityLog;
use App\Models\TahunPelajaran;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Get tahun pelajaran aktif
        $tahunPelajaranAktif = TahunPelajaran::where('is_active', true)->first();
        
        $stats = [
            'total_siswa' => Siswa::count(),
            'total_admin' => User::where('role', '!=', 'siswa')->count(),
            'siswa_aktif' => Siswa::whereHas('user', function($q) {
                $q->where('is_first_login', false);
            })->count(),
            'recent_activities' => ActivityLog::with('user')
                ->latest()
                ->take(10)
                ->get(),
            'online_count' => UserSession::online()->distinct('user_id')->count('user_id'),
        ];

        return view('admin.dashboard', compact('stats', 'tahunPelajaranAktif'));
    }

    public function onlineUsers()
    {
        $sessions = UserSession::online()
            ->with(['user.siswa', 'user.gtk'])
            ->orderByDesc('last_activity')
            ->get()
            ->unique('user_id')
            ->values();

        $users = $sessions->map(function ($session) {
            $user = $session->user;
            if (!$user) return null;

            // Resolve photo — same priority as profile page:
            // 1. users.avatar (uploaded via /admin/profile)
            // 2. siswa.foto_profile (for siswa accounts)
            // 3. gtk.foto_profile (for GTK accounts)
            // 4. ui-avatars fallback
            if ($user->avatar) {
                $photo = asset('storage/avatars/' . $user->avatar);
            } elseif ($user->role === 'siswa' && $user->siswa?->foto_profile) {
                $photo = $user->siswa->foto_profile_url;
            } elseif ($user->gtk?->foto_profile) {
                $photo = $user->gtk->foto_profile_url;
            } else {
                $bg = match(true) {
                    in_array($user->role, ['super_admin', 'admin']) => '5b63f1',
                    in_array($user->role, ['guru', 'gtk'])           => '2dc38b',
                    $user->role === 'siswa'                          => 'f4767d',
                    default                                          => '64748b',
                };
                $photo = 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=80&background=' . $bg . '&color=fff&bold=true';
            }

            $roleLabel = match($user->role) {
                'super_admin' => 'Super Admin',
                'admin' => 'Admin',
                'operator' => 'Operator',
                'siswa' => 'Siswa',
                'gtk' => 'GTK',
                default => ucfirst($user->role),
            };

            // Add Spatie role if available
            $spatieRole = $user->roles->first()?->name;
            if ($spatieRole && $spatieRole !== ucfirst($user->role)) {
                $roleLabel = $spatieRole;
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $roleLabel,
                'photo' => $photo,
                'device_icon' => $session->device_icon,
                'browser_icon' => $session->browser_icon,
                'last_activity' => $session->last_activity?->diffForHumans(),
                'last_activity_ts' => $session->last_activity?->timestamp,
            ];
        })->filter()->values();

        return response()->json([
            'users' => $users,
            'total' => $users->count(),
            'updated_at' => now()->format('H:i:s'),
        ]);
    }
}
