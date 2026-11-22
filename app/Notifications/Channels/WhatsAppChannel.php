<?php

namespace App\Notifications\Channels;

use App\Notifications\Support\NotificationFailureLogger;
use App\Services\WhatsApp\WhatsAppMessage;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Notifications\Notification;
use RuntimeException;
use Throwable;

class WhatsAppChannel
{
    public function __construct(
        protected WhatsAppService $service,
        protected NotificationFailureLogger $failureLogger,
    ) {}

    public function send(
        object $notifiable,
        Notification $notification,
    ): void {
        $recipient = null;

        try {
            if (!method_exists($notification, 'toWhatsApp')) {
                throw new RuntimeException(
                    'Notification must implement toWhatsApp().'
                );
            }

            if (!method_exists($notifiable, 'routeNotificationForWhatsApp')) {
                throw new RuntimeException(
                    'The notifiable model must implement routeNotificationForWhatsApp().'
                );
            }

            $message = $notification->toWhatsApp($notifiable);

            if (!$message instanceof WhatsAppMessage) {
                throw new RuntimeException(
                    'The toWhatsApp() method must return a WhatsAppMessage instance.'
                );
            }

            $recipient = $notifiable->routeNotificationForWhatsApp(
                $notification
            );

            if (!is_string($recipient) || trim($recipient) === '') {
                throw new RuntimeException(
                    'No valid WhatsApp recipient was configured.'
                );
            }

            $this->service->send(
                recipient: $recipient,
                message: $message,
            );
        } catch (Throwable $exception) {
            $this->failureLogger->log(
                channel: 'whatsapp',
                notifiable: $notifiable,
                notification: $notification,
                exception: $exception,
                recipient: $recipient,
            );
        }
    }
}