<?php

namespace App\Notifications\Channels;

use App\Notifications\Support\NotificationFailureLogger;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Notification;
use RuntimeException;
use Throwable;

class MailNotificationChannel
{
    public function __construct(
        protected MailChannel $mailChannel,
        protected NotificationFailureLogger $failureLogger,
    ) {}

    public function send(
        object $notifiable,
        Notification $notification
    ): void {
        $recipient = null;

        try {
            if (!method_exists($notification, 'toMail')) {
                throw new RuntimeException(
                    'Notification must implement toMail().'
                );
            }

            $recipient = $notifiable->routeNotificationFor(
                'mail',
                $notification
            );

            if (empty($recipient)) {
                throw new RuntimeException(
                    'No email recipient was configured.'
                );
            }

            $this->mailChannel->send(
                $notifiable,
                $notification
            );
        } catch (Throwable $exception) {
            $this->failureLogger->log(
                channel: 'email',
                notifiable: $notifiable,
                notification: $notification,
                exception: $exception,
                recipient: $recipient,
            );
        }
    }
}
