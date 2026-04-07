<?php

namespace App\Observers;

use App\Models\Notification;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Log;

class NotificationObserver
{
    private PushNotificationService $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Duyuru olusturuldugunda
     */
    public function created(Notification $notification): void
    {
        Log::info("Notification created, sending push notification", [
            'notification_id' => $notification->id,
            'title' => $notification->title,
            'target_type' => $notification->target_type,
            'target_id' => $notification->target_id
        ]);

        // Push notification gonder
        try {
            $results = $this->pushService->sendNotificationByType($notification->type, [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'target_type' => $notification->target_type,
                'target_id' => $notification->target_id,
            ]);

            Log::info("Push notification sent", [
                'notification_id' => $notification->id,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to send push notification", [
                'notification_id' => $notification->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Duyuru guncellendiginde
     */
    public function updated(Notification $notification): void
    {
        // Sadece status 'sent' olarak degistirildiginde push notification gonder
        if ($notification->isDirty('status') && $notification->status === 'sent') {
            $this->created($notification);
        }
    }
}
