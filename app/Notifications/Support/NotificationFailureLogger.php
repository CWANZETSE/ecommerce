<?php

namespace App\Notifications\Support;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationFailureLogger
{
    public function log(
        string $channel,
        object $notifiable,
        Notification $notification,
        Throwable $exception,
        mixed $recipient = null,
    ): void {
        Log::error('Notification delivery failed.', [
            'channel' => $channel,
            'notification' => class_basename($notification),
            'notification_id' => $notification->id ?? null,
            'recipient_id' => $notifiable->getKey(),
            'recipient' => $recipient,
            'error' => $exception->getMessage(),
        ]);
    }
}
