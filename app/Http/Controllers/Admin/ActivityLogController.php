<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    /**
     * Display activity logs page
     */
    public function index()
    {
        return view('admin.activity_logs.index');
    }

    /**
     * Get activity logs data for DataTables
     */
    public function getData(Request $request)
    {
        $query = ActivityLog::with('user')->select('activity_logs.*');

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by activity type
        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by device type
        if ($request->filled('device_type')) {
            $query->where('device_type', $request->device_type);
        }

        // Filter by location
        if ($request->filled('country_code')) {
            $query->where('country_code', $request->country_code);
        }

        return DataTables::of($query)
            ->addColumn('user_info', function ($log) {
                if ($log->user) {
                    $role = $log->user->roles()->first()?->name ?? 'N/A';
                    return '
                        <div class="user-info">
                            <strong>' . e($log->user->name) . '</strong><br>
                            <small class="text-muted">' . e($log->user->username) . '</small><br>
                            <span class="badge badge-info">' . e($role) . '</span>
                        </div>
                    ';
                }
                return '<span class="text-muted">Unknown User</span>';
            })
            ->addColumn('activity', function ($log) {
                $activityBadge = match($log->activity_type) {
                    'login' => 'success',
                    'logout' => 'secondary',
                    'upload_foto', 'upload_dokumen' => 'primary',
                    'update_data_diri', 'update_data_ortu' => 'warning',
                    'delete_dokumen' => 'danger',
                    default => 'info',
                };

                return '
                    <span class="badge badge-' . $activityBadge . '">' . e($log->activity_type) . '</span><br>
                    <small class="text-muted">' . e($log->description) . '</small>
                ';
            })
            ->addColumn('device_info', function ($log) {
                $deviceIcon = ActivityLogService::getDeviceIcon($log->device_type);
                $browserIcon = ActivityLogService::getBrowserIcon($log->browser);
                
                return '
                    <div class="device-info">
                        ' . $deviceIcon . ' ' . e($log->device_type ?? 'unknown') . '<br>
                        ' . $browserIcon . ' ' . e($log->browser ?? 'unknown') . ' ' . e($log->browser_version ?? '') . '<br>
                        <small class="text-muted">' . e($log->platform ?? 'unknown') . ' ' . e($log->platform_version ?? '') . '</small>
                    </div>
                ';
            })
            ->addColumn('location', function ($log) {
                if ($log->city || $log->country) {
                    $location = [];
                    if ($log->city) $location[] = $log->city;
                    if ($log->region && $log->region != $log->city) $location[] = $log->region;
                    if ($log->country) $location[] = $log->country;
                    
                    $locationStr = implode(', ', $location);
                    $flag = $log->country_code ? '<i class="flag-icon flag-icon-' . strtolower($log->country_code) . '"></i> ' : '';
                    
                    return '
                        <div class="location-info">
                            ' . $flag . e($locationStr) . '<br>
                            <small class="text-muted">IP: ' . e($log->ip_address) . '</small>
                        </div>
                    ';
                }
                return '<small class="text-muted">IP: ' . e($log->ip_address) . '</small>';
            })
            ->addColumn('changes', function ($log) {
                if ($log->changed_fields && !empty($log->changed_fields)) {
                    $changes = '<button class="btn btn-sm btn-info" onclick="showChanges(' . $log->id . ')">
                        <i class="fas fa-eye"></i> Lihat Perubahan (' . count($log->changed_fields) . ')
                    </button>';
                    return $changes;
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('timestamp', function ($log) {
                return '
                    <div class="timestamp-info">
                        ' . $log->created_at->format('d/m/Y') . '<br>
                        <small class="text-muted">' . $log->created_at->format('H:i:s') . '</small><br>
                        <small class="text-info">' . $log->created_at->diffForHumans() . '</small>
                    </div>
                ';
            })
            ->addColumn('action', function ($log) {
                return '
                    <button class="btn btn-sm btn-primary" onclick="showDetail(' . $log->id . ')">
                        <i class="fas fa-info-circle"></i> Detail
                    </button>
                ';
            })
            ->rawColumns(['user_info', 'activity', 'device_info', 'location', 'changes', 'timestamp', 'action'])
            ->make(true);
    }

    /**
     * Get detail of a specific log
     */
    public function show($id)
    {
        $log = ActivityLog::with('user')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'log' => $log,
            'user' => $log->user,
        ]);
    }

    /**
     * Get statistics
     */
    public function statistics(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->subDays(30));
        $dateTo = $request->input('date_to', Carbon::now());

        $stats = [
            'total_activities' => ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'unique_users' => ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])->distinct('user_id')->count('user_id'),
            'by_device' => ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('device_type', \DB::raw('count(*) as total'))
                ->groupBy('device_type')
                ->get(),
            'by_activity_type' => ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('activity_type', \DB::raw('count(*) as total'))
                ->groupBy('activity_type')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get(),
            'by_country' => ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])
                ->whereNotNull('country')
                ->select('country', 'country_code', \DB::raw('count(*) as total'))
                ->groupBy('country', 'country_code')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Export logs to CSV
     */
    public function export(Request $request)
    {
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->get();

        $filename = 'activity_logs_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Timestamp', 'User', 'Username', 'Role', 'Activity Type', 'Description',
                'Device', 'Browser', 'Platform', 'IP Address', 'Country', 'City',
                'Changed Fields'
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user->name ?? 'Unknown',
                    $log->user->username ?? 'N/A',
                    $log->user->roles()->first()?->name ?? 'N/A',
                    $log->activity_type,
                    $log->description,
                    $log->device_type ?? '-',
                    ($log->browser ?? '-') . ' ' . ($log->browser_version ?? ''),
                    ($log->platform ?? '-') . ' ' . ($log->platform_version ?? ''),
                    $log->ip_address,
                    $log->country ?? '-',
                    $log->city ?? '-',
                    $log->changed_fields ? implode(', ', $log->changed_fields) : '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
