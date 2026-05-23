<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamNotification;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExamNotificationController extends Controller
{
    /**
     * Display notification management page
     */
    public function index()
    {
        $notifications = ExamNotification::orderBy('created_at', 'desc')
            ->paginate(15);

        // Check if FCM is configured
        $fcmService = new FcmService();
        $fcmConfigured = $fcmService->isConfigured();

        return view('admin.exam-notifications.index', compact('notifications', 'fcmConfigured'));
    }

    /**
     * Store a new notification and push via FCM
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

        $notification = ExamNotification::create($validated);

        // Send real-time push notification via FCM
        $fcmSent = false;
        try {
            $fcm = new FcmService();
            if ($fcm->isConfigured()) {
                $fcmSent = $fcm->sendToAllDevices(
                    $validated['title'],
                    $validated['message'],
                    $validated['type'],
                    $notification->id,
                );
            }
        } catch (\Exception $e) {
            Log::warning('[ExamNotification] FCM push failed: ' . $e->getMessage());
        }

        $successMsg = 'Notifikasi berhasil dikirim ke semua device.';
        if ($fcmSent) {
            $successMsg = 'Notifikasi berhasil dikirim secara REALTIME ke semua device via push notification.';
        } else {
            $successMsg = 'Notifikasi berhasil disimpan, tetapi push FCM belum aktif sehingga device tidak akan menerima notifikasi realtime.';
        }

        return redirect()->route('admin.exam-notifications.index')
            ->with('success', $successMsg);
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
