<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Firebase Cloud Messaging service for sending push notifications
 * to ExaManmet exam browser devices.
 *
 * Uses FCM HTTP v1 API with Google OAuth2 service account authentication.
 * Lightweight — only requires google/auth package.
 */
class FcmService
{
    protected ?string $projectId = null;
    protected ?ServiceAccountCredentials $credentials = null;

    public function __construct()
    {
        $credentialsPath = config('firebase.credentials');

        if ($credentialsPath && file_exists($credentialsPath)) {
            try {
                $json = json_decode(file_get_contents($credentialsPath), true);
                $this->projectId = $json['project_id'] ?? null;

                $this->credentials = new ServiceAccountCredentials(
                    'https://www.googleapis.com/auth/firebase.messaging',
                    $json
                );
            } catch (\Exception $e) {
                Log::error('[FCM] Failed to initialize: ' . $e->getMessage());
            }
        } else {
            Log::debug('[FCM] Service account file not found at: ' . ($credentialsPath ?? 'null'));
        }
    }

    /**
     * Check if FCM is properly configured and ready to send.
     */
    public function isConfigured(): bool
    {
        return $this->credentials !== null && $this->projectId !== null;
    }

    /**
     * Get OAuth2 access token for FCM API.
     */
    protected function getAccessToken(): ?string
    {
        try {
            $token = $this->credentials->fetchAuthToken();
            return $token['access_token'] ?? null;
        } catch (\Exception $e) {
            Log::error('[FCM] Token fetch failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send a push notification to a topic via FCM HTTP v1 API.
     *
     * @param string $topic   FCM topic name (e.g., 'examanmet_all')
     * @param string $title   Notification title
     * @param string $message Notification body message
     * @param string $type    Notification type: info, warning, urgent
     * @param array  $extraData Additional data payload
     * @return bool Whether the send was successful
     */
    public function sendToTopic(string $topic, string $title, string $message, string $type = 'info', array $extraData = []): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('[FCM] Cannot send — not configured');
            return false;
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            Log::error('[FCM] Cannot send — failed to get access token');
            return false;
        }

        // Set Android priority based on notification type
        $priority = in_array($type, ['urgent', 'warning']) ? 'HIGH' : 'NORMAL';
        $channelId = match ($type) {
            'urgent' => 'exam_urgent',
            'warning' => 'exam_warning',
            default => 'exam_info',
        };

        $payload = [
            'message' => [
                'topic' => $topic,
                'notification' => [
                    'title' => $title,
                    'body' => $message,
                ],
                'data' => array_merge([
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'sent_at' => now()->toIso8601String(),
                ], array_map('strval', $extraData)),
                'android' => [
                    'priority' => $priority,
                    'notification' => [
                        'channel_id' => $channelId,
                        'sound' => 'default',
                    ],
                ],
            ],
        ];

        try {
            $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

            $response = Http::withToken($accessToken)
                ->timeout(10)
                ->post($url, $payload);

            if ($response->successful()) {
                Log::info("[FCM] Sent to topic '{$topic}': {$title} (type: {$type})");
                return true;
            }

            Log::error('[FCM] API error: ' . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error('[FCM] Send failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to all ExaManmet devices.
     */
    public function sendToAllDevices(string $title, string $message, string $type = 'info', string $notificationId = ''): bool
    {
        return $this->sendToTopic('examanmet_all', $title, $message, $type, [
            'id' => $notificationId,
        ]);
    }
}
