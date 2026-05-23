<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamNotification;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class ExamNotificationController extends Controller
{
    /**
     * Display notification management page
     */
    public function index()
    {
        $notifications = ExamNotification::with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Check if FCM is configured
        $fcmService = new FcmService();
        $fcmConfigured = $fcmService->isConfigured();

        return view('admin.exam-notifications.index', compact('notifications', 'fcmConfigured'));
    }

    /**
     * Store a new notification and push via FCM
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'display_seconds' => 'required|integer|min:3|max:60',
            'type' => 'required|in:info,warning,urgent',
            'target' => 'required|in:all,exam_active',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $validated['sent_by'] = auth()->id();
        $validated['is_active'] = true;

        $notification = ExamNotification::create($validated);

        $fcmSent = $this->pushNotification($notification);

        $successMsg = 'Notifikasi berhasil dikirim ke semua device.';
        if ($fcmSent) {
            $successMsg = 'Notifikasi berhasil dikirim secara REALTIME ke semua device via push notification.';
        } else {
            $successMsg = 'Notifikasi berhasil disimpan, tetapi push FCM belum aktif sehingga device tidak akan menerima notifikasi realtime.';
        }

        return redirect()->route('admin.exam-notifications.index')
            ->with('success', $successMsg);
    }

    public function resend(ExamNotification $examNotification): RedirectResponse
    {
        $resent = $examNotification->replicate(['created_at', 'updated_at', 'deleted_at']);
        $resent->sent_by = auth()->id();
        $resent->is_active = true;
        $resent->scheduled_at = null;
        $resent->expires_at = $examNotification->expires_at && $examNotification->expires_at->isPast()
            ? now()->addMinutes(15)
            : $examNotification->expires_at;
        $resent->save();

        $fcmSent = $this->pushNotification($resent);

        return redirect()->route('admin.exam-notifications.index')
            ->with(
                $fcmSent ? 'success' : 'warning',
                $fcmSent
                    ? 'Notifikasi berhasil dikirim ulang ke aplikasi.'
                    : 'Riwayat notifikasi berhasil diduplikasi, tetapi push realtime belum berhasil dikirim.'
            );
    }

    /**
     * Deactivate a notification
     */
    public function destroy(ExamNotification $examNotification): RedirectResponse
    {
        $examNotification->update(['is_active' => false]);

        return redirect()->route('admin.exam-notifications.index')
            ->with('success', 'Notifikasi berhasil dinonaktifkan.');
    }

    /**
     * Permanently delete a notification
     */
    public function forceDelete(string $id): RedirectResponse
    {
        $notification = ExamNotification::withTrashed()->findOrFail($id);
        $notification->forceDelete();

        return redirect()->route('admin.exam-notifications.index')
            ->with('success', 'Notifikasi berhasil dihapus permanen.');
    }

    protected function pushNotification(ExamNotification $notification): bool
    {
        try {
            $fcm = new FcmService();

            if (!$fcm->isConfigured()) {
                return false;
            }

            return $fcm->sendToAllDevices(
                $notification->title,
                $notification->message,
                $notification->type,
                $notification->id,
                [
                    'display_seconds' => $notification->display_seconds,
                    'target' => $notification->target,
                ],
            );
        } catch (\Exception $e) {
            Log::warning('[ExamNotification] FCM push failed: ' . $e->getMessage(), [
                'notification_id' => $notification->id,
            ]);

            return false;
        }
    }
}
