<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamNotification;
use Illuminate\Http\Request;

class ExamNotificationController extends Controller
{
    /**
     * Display notification management page
     */
    public function index()
    {
        $notifications = ExamNotification::orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.exam-notifications.index', compact('notifications'));
    }

    /**
     * Store a new notification
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'type' => 'required|in:info,warning,urgent',
            'target' => 'required|in:all,exam_active',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $validated['sent_by'] = auth()->id();
        $validated['is_active'] = true;

        ExamNotification::create($validated);

        return redirect()->route('admin.exam-notifications.index')
            ->with('success', 'Notifikasi berhasil dikirim ke semua device.');
    }

    /**
     * Deactivate a notification
     */
    public function destroy(ExamNotification $examNotification)
    {
        $examNotification->update(['is_active' => false]);

        return redirect()->route('admin.exam-notifications.index')
            ->with('success', 'Notifikasi berhasil dinonaktifkan.');
    }

    /**
     * Permanently delete a notification
     */
    public function forceDelete(string $id)
    {
        $notification = ExamNotification::withTrashed()->findOrFail($id);
        $notification->forceDelete();

        return redirect()->route('exam-notifications.index')
            ->with('success', 'Notifikasi berhasil dihapus permanen.');
    }
}
