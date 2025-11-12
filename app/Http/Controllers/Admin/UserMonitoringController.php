<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class UserMonitoringController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->can('admin-access')) {
                abort(403, 'Akses ditolak. Hanya Admin yang dapat mengakses monitoring users.');
            }
            return $next($request);
        });
    }

    /**
     * Display list of users with their online status
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Get latest session untuk setiap user
            $sessions = UserSession::with('user')
                ->whereIn('id', function($query) {
                    $query->selectRaw('MAX(id)')
                        ->from('user_sessions')
                        ->groupBy('user_id');
                })
                ->orderBy('last_activity', 'desc')
                ->get();

            return DataTables::of($sessions)
                ->addIndexColumn()
                ->addColumn('user_info', function ($session) {
                    $user = $session->user;
                    if (!$user) return '-';
                    
                    $role = $user->getRoleNames()->first() ?? 'No Role';
                    $badge = $this->getRoleBadge($role);
                    
                    return "
                        <div class='d-flex align-items-center'>
                            <div class='mr-2'>
                                <strong>{$user->name}</strong><br>
                                <small class='text-muted'>{$user->email}</small><br>
                                <span class='badge {$badge}'>{$role}</span>
                            </div>
                        </div>
                    ";
                })
                ->addColumn('status', function ($session) {
                    $isOnline = $session->isStillOnline();
                    
                    if ($isOnline) {
                        return "<span class='badge badge-success'><i class='fas fa-circle'></i> Online</span>";
                    } else {
                        return "<span class='badge badge-secondary'><i class='fas fa-circle'></i> Offline</span>";
                    }
                })
                ->addColumn('device_info', function ($session) {
                    return "
                        <div>
                            <i class='{$session->device_icon} text-primary'></i> 
                            <strong>" . ucfirst($session->device_type ?? '-') . "</strong><br>
                            <i class='{$session->browser_icon}'></i> {$session->browser}<br>
                            <i class='{$session->platform_icon}'></i> {$session->platform}
                        </div>
                    ";
                })
                ->addColumn('location_info', function ($session) {
                    $ip = $session->ip_address ?? '-';
                    $location = [];
                    
                    if ($session->city) $location[] = $session->city;
                    if ($session->country) $location[] = $session->country;
                    
                    $locationStr = !empty($location) ? implode(', ', $location) : 'Unknown';
                    
                    return "
                        <div>
                            <i class='fas fa-network-wired'></i> <strong>{$ip}</strong><br>
                            <i class='fas fa-map-marker-alt'></i> <small>{$locationStr}</small>
                        </div>
                    ";
                })
                ->addColumn('last_activity', function ($session) {
                    if (!$session->last_activity) return '-';
                    
                    $diff = $session->last_activity->diffForHumans();
                    $formatted = $session->last_activity->format('d M Y H:i:s');
                    
                    return "
                        <span data-toggle='tooltip' title='{$formatted}'>
                            {$diff}
                        </span>
                    ";
                })
                ->addColumn('action', function ($session) {
                    return "
                        <button class='btn btn-sm btn-info view-detail' data-id='{$session->id}' data-user-id='{$session->user_id}'>
                            <i class='fas fa-eye'></i> Detail
                        </button>
                    ";
                })
                ->rawColumns(['user_info', 'status', 'device_info', 'location_info', 'last_activity', 'action'])
                ->make(true);
        }

        // Statistics
        $totalUsers = User::count();
        $onlineUsers = UserSession::online()->distinct('user_id')->count('user_id');
        $totalSessions = UserSession::count();
        
        return view('admin.monitoring.index', compact('totalUsers', 'onlineUsers', 'totalSessions'));
    }

    /**
     * Get user session detail
     */
    public function show($userId)
    {
        $user = User::findOrFail($userId);
        
        // Get all sessions untuk user ini
        $sessions = UserSession::where('user_id', $userId)
            ->orderBy('last_activity', 'desc')
            ->limit(10)
            ->get();
        
        $currentSession = $sessions->first();
        
        return response()->json([
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first() ?? 'No Role'
            ],
            'current_session' => $currentSession ? [
                'is_online' => $currentSession->isStillOnline(),
                'device_type' => $currentSession->device_type,
                'browser' => $currentSession->browser,
                'platform' => $currentSession->platform,
                'ip_address' => $currentSession->ip_address,
                'location' => ($currentSession->city ? $currentSession->city . ', ' : '') . $currentSession->country,
                'last_activity' => $currentSession->last_activity?->format('d M Y H:i:s'),
                'last_activity_human' => $currentSession->last_activity?->diffForHumans()
            ] : null,
            'recent_sessions' => $sessions->map(function($session) {
                return [
                    'device_type' => $session->device_type,
                    'browser' => $session->browser,
                    'platform' => $session->platform,
                    'ip_address' => $session->ip_address,
                    'last_activity' => $session->last_activity?->format('d M Y H:i:s'),
                    'is_online' => $session->isStillOnline()
                ];
            })
        ]);
    }

    /**
     * Get online users count (for real-time updates)
     */
    public function getOnlineCount()
    {
        $onlineUsers = UserSession::online()->distinct('user_id')->count('user_id');
        
        return response()->json([
            'online_count' => $onlineUsers
        ]);
    }

    /**
     * Force logout user session
     */
    public function forceLogout($userId)
    {
        UserSession::where('user_id', $userId)->update([
            'is_online' => false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil di-logout'
        ]);
    }

    /**
     * Get role badge class
     */
    private function getRoleBadge($role)
    {
        return match(strtolower($role)) {
            'super admin' => 'badge-danger',
            'admin' => 'badge-primary',
            'guru' => 'badge-info',
            'siswa' => 'badge-success',
            'wali kelas' => 'badge-warning',
            default => 'badge-secondary'
        };
    }
}
