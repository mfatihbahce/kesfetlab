<?php

namespace App\Services;

use App\Models\ParentUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PushNotificationService
{
    private string $serverKey;
    private string $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

    public function __construct()
    {
        // FCM Server Key - gerçek uygulamada .env'den alınmalı
        $this->serverKey = env('FCM_SERVER_KEY', 'AAAA_dummy_key_for_development');
    }

    /**
     * Tek bir kullanıcıya push notification gönder
     */
    public function sendToUser(ParentUser $user, string $title, string $body, array $data = []): bool
    {
        if (empty($user->fcm_token)) {
            Log::warning("User {$user->id} has no FCM token");
            return false;
        }

        return $this->sendNotification($user->fcm_token, $title, $body, $data);
    }

    /**
     * Birden fazla kullanıcıya push notification gönder
     */
    public function sendToUsers(array $users, string $title, string $body, array $data = []): array
    {
        $results = [];
        
        foreach ($users as $user) {
            if ($user instanceof ParentUser && !empty($user->fcm_token)) {
                $results[$user->id] = $this->sendNotification($user->fcm_token, $title, $body, $data);
            } else {
                $results[$user->id ?? 'unknown'] = false;
            }
        }

        return $results;
    }

    /**
     * Tüm aktif velilere push notification gönder
     */
    public function sendToAllParents(string $title, string $body, array $data = []): array
    {
        $parents = ParentUser::where('status', 'active')
                            ->whereNotNull('fcm_token')
                            ->get();

        if ($parents->isEmpty()) {
            Log::info('No parents with FCM tokens found');
            return [];
        }

        Log::info("Sending push notification to {$parents->count()} parents");
        return $this->sendToUsers($parents->toArray(), $title, $body, $data);
    }

    /**
     * Belirli bir grubun velilerine push notification gönder
     */
    public function sendToGroupParents(int $groupId, string $title, string $body, array $data = []): array
    {
        $parents = ParentUser::whereHas('students.enrollments', function ($query) use ($groupId) {
            $query->where('group_id', $groupId)
                  ->where('status', 'approved')
                  ->where('is_active', true);
        })
        ->where('status', 'active')
        ->whereNotNull('fcm_token')
        ->get();

        if ($parents->isEmpty()) {
            Log::info("No parents with FCM tokens found for group {$groupId}");
            return [];
        }

        Log::info("Sending push notification to {$parents->count()} parents for group {$groupId}");
        return $this->sendToUsers($parents->toArray(), $title, $body, $data);
    }

    /**
     * Belirli bir öğrencinin velisine push notification gönder
     */
    public function sendToStudentParents(int $studentId, string $title, string $body, array $data = []): array
    {
        $parents = ParentUser::whereHas('students', function ($query) use ($studentId) {
            $query->where('students.id', $studentId);
        })
        ->where('status', 'active')
        ->whereNotNull('fcm_token')
        ->get();

        if ($parents->isEmpty()) {
            Log::info("No parents with FCM tokens found for student {$studentId}");
            return [];
        }

        Log::info("Sending push notification to {$parents->count()} parents for student {$studentId}");
        return $this->sendToUsers($parents->toArray(), $title, $body, $data);
    }

    /**
     * FCM'e push notification gönder
     */
    private function sendNotification(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        try {
            Log::info("Sending push notification", [
                'token' => substr($fcmToken, 0, 20) . '...',
                'title' => $title,
                'body' => $body,
                'data' => $data
            ]);

            $payload = [
                'to' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'icon' => 'ic_launcher',
                    'sound' => 'default',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ],
                'data' => array_merge($data, [
                    'title' => $title,
                    'body' => $body,
                    'timestamp' => now()->toISOString()
                ]),
                'priority' => 'high'
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->fcmUrl, $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info("Push notification sent successfully", [
                    'response' => $responseData
                ]);
                return true;
            } else {
                Log::error("Failed to send push notification", [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error("Exception while sending push notification", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Duyuru tipine göre push notification gönder
     */
    public function sendNotificationByType(string $type, array $notificationData): array
    {
        $title = $notificationData['title'] ?? 'KeşfetLAB';
        $body = $notificationData['message'] ?? 'Yeni bildirim';
        $data = [
            'type' => $type,
            'notification_id' => $notificationData['id'] ?? null,
        ];

        switch ($notificationData['target_type']) {
            case 'all':
                return $this->sendToAllParents($title, $body, $data);
                
            case 'group':
                if (isset($notificationData['target_id'])) {
                    return $this->sendToGroupParents($notificationData['target_id'], $title, $body, $data);
                }
                break;
                
            case 'student':
                if (isset($notificationData['target_id'])) {
                    return $this->sendToStudentParents($notificationData['target_id'], $title, $body, $data);
                }
                break;
                
            case 'parent':
                if (isset($notificationData['target_id'])) {
                    $parent = ParentUser::find($notificationData['target_id']);
                    if ($parent) {
                        return [$parent->id => $this->sendToUser($parent, $title, $body, $data)];
                    }
                }
                break;
        }

        return [];
    }
}