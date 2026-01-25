<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class EmailLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage-settings');
    }

    /**
     * Display email logs listing
     */
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson() || $request->has('draw')) {
            $query = EmailLog::with('sender')
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('status_badge', function ($row) {
                    $badge = $row->status_badge;
                    $icon = match($row->status) {
                        'sent' => 'check-circle',
                        'failed' => 'times-circle',
                        'pending' => 'clock',
                        default => 'question-circle',
                    };
                    return '<span class="badge badge-' . $badge . '"><i class="fas fa-' . $icon . '"></i> ' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('type_label', function ($row) {
                    $colors = [
                        'password_reset' => 'info',
                        'notification' => 'primary',
                        'test' => 'warning',
                        'general' => 'secondary',
                    ];
                    $color = $colors[$row->type] ?? 'secondary';
                    return '<span class="badge badge-' . $color . '">' . $row->type_label . '</span>';
                })
                ->addColumn('sender_name', function ($row) {
                    return $row->sender ? $row->sender->name : '<span class="text-muted">System</span>';
                })
                ->addColumn('created_at_formatted', function ($row) {
                    return $row->created_at->format('d/m/Y H:i:s');
                })
                ->addColumn('action', function ($row) {
                    return '<button type="button" class="btn btn-info btn-sm" onclick="showDetail(\'' . $row->id . '\')">
                                <i class="fas fa-eye"></i>
                            </button>';
                })
                ->rawColumns(['status_badge', 'type_label', 'sender_name', 'action'])
                ->make(true);
        }

        // Get statistics
        $stats = [
            'total' => EmailLog::count(),
            'sent' => EmailLog::where('status', 'sent')->count(),
            'failed' => EmailLog::where('status', 'failed')->count(),
            'pending' => EmailLog::where('status', 'pending')->count(),
            'today' => EmailLog::whereDate('created_at', today())->count(),
        ];

        return view('admin.email-logs.index', compact('stats'));
    }

    /**
     * Get email log detail
     */
    public function show(EmailLog $emailLog)
    {
        $emailLog->load('sender');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $emailLog->id,
                'to_email' => $emailLog->to_email,
                'to_name' => $emailLog->to_name,
                'from_email' => $emailLog->from_email,
                'from_name' => $emailLog->from_name,
                'subject' => $emailLog->subject,
                'body' => $emailLog->body,
                'type' => $emailLog->type,
                'type_label' => $emailLog->type_label,
                'status' => $emailLog->status,
                'status_badge' => $emailLog->status_badge,
                'error_message' => $emailLog->error_message,
                'sender_name' => $emailLog->sender ? $emailLog->sender->name : 'System',
                'sent_at' => $emailLog->sent_at ? $emailLog->sent_at->format('d/m/Y H:i:s') : null,
                'created_at' => $emailLog->created_at->format('d/m/Y H:i:s'),
            ]
        ]);
    }

    /**
     * Delete old email logs
     */
    public function cleanup(Request $request)
    {
        $days = $request->input('days', 30);
        
        $deleted = EmailLog::where('created_at', '<', now()->subDays($days))->delete();

        return response()->json([
            'success' => true,
            'message' => "Berhasil menghapus {$deleted} log email yang lebih dari {$days} hari."
        ]);
    }
}
