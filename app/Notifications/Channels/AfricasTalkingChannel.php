<?php

namespace App\Notifications\Channels;

use App\Services\AfricasTalking\AfricasTalkingMessage;
use App\Services\AfricasTalking\AfricasTalkingService;
use Illuminate\Notifications\Notification;
use RuntimeException;

class AfricasTalkingChannel
{
    public function __construct(
        protected AfricasTalkingService $service
    ) {}

    public function send(
        object $notifiable,
        Notification $notification
    ): array {
        if (!method_exists($notification, 'toAfricasTalking')) {
            throw new RuntimeException(
                'Notification must implement toAfricasTalking().'
            );
        }

        $message = $notification->toAfricasTalking($notifiable);

        if (!$message instanceof AfricasTalkingMessage) {
            throw new RuntimeException(
                'toAfricasTalking() must return an AfricasTalkingMessage.'
            );
        }

        $recipients = $notifiable->routeNotificationForAfricasTalking(
            $notification
        );

        if (!$recipients) {
            throw new RuntimeException(
                'No Africa\'s Talking recipient was configured.'
            );
        }

        return $this->service->send(
            recipients: $recipients,
            message: $message->content,
            senderId: $message->from,
            enqueue: $message->enqueue,
        );
    }
}