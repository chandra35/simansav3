<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Gtk;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\UserSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $tahunPelajaranAktif = TahunPelajaran::where('is_active', true)->first();

        $siswaTahunAktif = Siswa::query()
            ->when(
                $tahunPelajaranAktif,
                fn ($query) => $query->whereHas('kelasTahunAktif'),
                fn ($query) => $query->whereRaw('1 = 0')
            );

        $stats = [
            'total_siswa' => (clone $siswaTahunAktif)->count(),
            'total_gtk' => Gtk::count(),
            'siswa_aktif' => (clone $siswaTahunAktif)->whereHas('user', function ($query) {
                $query->where('is_first_login', false);
            })->count(),
            'recent_activities' => ActivityLog::with('user')
                ->latest()
                ->take(10)
                ->get(),
            'online_count' => UserSession::online()->distinct('user_id')->count('user_id'),
        ];

        return view('admin.dashboard', compact('stats', 'tahunPelajaranAktif'));
    }

    public function onlineUsers(Request $request)
    {
        $perPage = min(max($request->integer('per_page', 8), 1), 50);
        $role = $request->string('role')->trim()->lower()->value();
        $search = $request->string('search')->trim()->value();
        $query = $this->latestOnlineSessionsQuery()
            ->with(['user.siswa', 'user.gtk', 'user.roles']);

        if ($search !== '') {
            $query->whereHas('user', function (Builder $userQuery) use ($search) {
                $userQuery->where(function (Builder $identityQuery) use ($search) {
                    $identityQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhereHas('siswa', fn (Builder $siswaQuery) => $siswaQuery->where('nisn', 'like', "%{$search}%"))
                        ->orWhereHas('gtk', fn (Builder $gtkQuery) => $gtkQuery
                            ->where('nip', 'like', "%{$search}%")
                            ->orWhere('nuptk', 'like', "%{$search}%"));
                });
            });
        }

        if (in_array($role, ['siswa', 'gtk', 'staff'], true)) {
            $query->whereHas('user', function (Builder $userQuery) use ($role) {
                match ($role) {
                    'siswa' => $userQuery->where('role', 'siswa'),
                    'gtk' => $userQuery->whereIn('role', ['gtk', 'guru']),
                    'staff' => $userQuery->whereNotIn('role', ['siswa', 'gtk', 'guru']),
                };
            });
        }

        $summary = $this->onlineSummary();
        $sessions = $query
            ->orderByDesc('user_sessions.last_activity')
            ->orderByDesc('user_sessions.id')
            ->paginate($perPage);

        $users = collect($sessions->items())
            ->map(fn (UserSession $session) => $this->serializeOnlineSession($session))
            ->filter()
            ->values();

        return response()->json([
            'users' => $users,
            'total' => $summary['all'],
            'summary' => $summary,
            'pagination' => [
                'current_page' => $sessions->currentPage(),
                'last_page' => $sessions->lastPage(),
                'per_page' => $sessions->perPage(),
                'from' => $sessions->firstItem(),
                'to' => $sessions->lastItem(),
                'filtered_total' => $sessions->total(),
            ],
            'updated_at' => now()->format('H:i:s'),
        ]);
    }

    private function latestOnlineSessionsQuery(): Builder
    {
        $threshold = now()->subMinutes(5);

        return UserSession::query()
            ->where('user_sessions.is_online', true)
            ->where('user_sessions.last_activity', '>=', $threshold)
            ->whereNotExists(function ($query) use ($threshold) {
                $query
                    ->selectRaw('1')
                    ->from('user_sessions as newer_session')
                    ->whereColumn('newer_session.user_id', 'user_sessions.user_id')
                    ->where('newer_session.is_online', true)
                    ->where('newer_session.last_activity', '>=', $threshold)
                    ->where(function ($newerQuery) {
                        $newerQuery
                            ->whereColumn('newer_session.last_activity', '>', 'user_sessions.last_activity')
                            ->orWhere(function ($sameActivityQuery) {
                                $sameActivityQuery
                                    ->whereColumn('newer_session.last_activity', 'user_sessions.last_activity')
                                    ->whereColumn('newer_session.id', '>', 'user_sessions.id');
                            });
                    });
            });
    }

    private function onlineSummary(): array
    {
        $summary = $this->latestOnlineSessionsQuery()
            ->join('users', 'users.id', '=', 'user_sessions.user_id')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN users.role = 'siswa' THEN 1 ELSE 0 END) as siswa,
                SUM(CASE WHEN users.role IN ('gtk', 'guru') THEN 1 ELSE 0 END) as gtk
            ")
            ->first();

        $all = (int) ($summary?->total ?? 0);
        $siswa = (int) ($summary?->siswa ?? 0);
        $gtk = (int) ($summary?->gtk ?? 0);

        return [
            'all' => $all,
            'siswa' => $siswa,
            'gtk' => $gtk,
            'staff' => max(0, $all - $siswa - $gtk),
        ];
    }

    private function serializeOnlineSession(UserSession $session): ?array
    {
        $user = $session->user;

        if (! $user) {
            return null;
        }

        if ($user->avatar) {
            $photo = asset('storage/avatars/'.$user->avatar);
        } elseif ($user->role === 'siswa' && $user->siswa?->foto_profile) {
            $photo = $user->siswa->foto_profile_url;
        } elseif ($user->gtk?->foto_profile) {
            $photo = $user->gtk->foto_profile_url;
        } else {
            $background = match (true) {
                in_array($user->role, ['super_admin', 'admin'], true) => '5b63f1',
                in_array($user->role, ['guru', 'gtk'], true) => '2dc38b',
                $user->role === 'siswa' => 'f4767d',
                default => '64748b',
            };
            $photo = 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=80&background='.$background.'&color=fff&bold=true';
        }

        $roleLabel = match ($user->role) {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'operator' => 'Operator',
            'siswa' => 'Siswa',
            'gtk', 'guru' => 'GTK',
            default => ucfirst((string) $user->role),
        };
        $spatieRole = $user->roles->first()?->name;

        if ($spatieRole && strcasecmp($spatieRole, $roleLabel) !== 0) {
            $roleLabel = $spatieRole;
        }

        $roleGroup = match (true) {
            $user->role === 'siswa' => 'siswa',
            in_array($user->role, ['gtk', 'guru'], true) => 'gtk',
            default => 'staff',
        };
        $deviceLabel = match (strtolower((string) $session->device_type)) {
            'mobile' => 'Ponsel',
            'tablet' => 'Tablet',
            default => 'Komputer',
        };
        $deviceDetails = collect([$session->browser, $session->platform])
            ->filter()
            ->implode(' · ');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $roleLabel,
            'role_group' => $roleGroup,
            'photo' => $photo,
            'device_icon' => $session->device_icon,
            'browser_icon' => $session->browser_icon,
            'device' => $deviceLabel,
            'device_details' => $deviceDetails ?: 'Perangkat tidak dikenali',
            'last_activity' => $session->last_activity?->diffForHumans(),
            'last_activity_time' => $session->last_activity?->format('H:i:s'),
            'last_activity_ts' => $session->last_activity?->timestamp,
        ];
    }
}
