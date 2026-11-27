<?php

namespace App\Notifications\Channels;

use App\Notifications\Support\NotificationFailureLogger;
use App\Services\AfricasTalking\AfricasTalkingMessage;
use App\Services\AfricasTalking\AfricasTalkingService;
use Illuminate\Notifications\Notification;
use RuntimeException;
use Throwable;

class AfricasTalkingChannel
{
    public function __construct(
        protected AfricasTalkingService $service,
        protected NotificationFailureLogger $failureLogger,
    ) {}

    public function send(
        object $notifiable,
        Notification $notification
    ): void {
        $recipients = null;

        try {
            if (!method_exists($notification, 'toAfricasTalking')) {
                throw new RuntimeException(
                    'Notification must implement toAfricasTalking().'
                );
            }

            if (!method_exists($notifiable, 'routeNotificationForAfricasTalking')) {
                throw new RuntimeException(
                    'Notifiable must implement routeNotificationForAfricasTalking().'
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

            if (empty($recipients)) {
                throw new RuntimeException(
                    'No Africa\'s Talking recipient was configured.'
                );
            }

            $result = $this->service->send(
                recipients: $recipients,
                message: $message->content,
                senderId: $message->from,
                enqueue: $message->enqueue,
            );

            $recipientResults = data_get(
                $result,
                'SMSMessageData.Recipients'
            );

            if (!is_array($recipientResults) || empty($recipientResults)) {
                throw new RuntimeException(
                    'Africa\'s Talking returned no recipient results.'
                );
            }

            foreach ($recipientResults as $recipientResult) {
                $statusCode = (int) ($recipientResult['statusCode'] ?? 0);

                if (in_array($statusCode, [100, 101, 102], true)) {
                    continue;
                }

                $recipientNumber = $recipientResult['number'] ?? 'Unknown';

                $this->failureLogger->log(
                    channel: 'sms',
                    notifiable: $notifiable,
                    notification: $notification,
                    exception: new RuntimeException(
                        sprintf(
                            'Africa\'s Talking SMS failed. Status code: %d. Status: %s.',
                            $statusCode,
                            $recipientResult['status'] ?? 'Unknown'
                        )
                    ),
                    recipient: $recipientNumber,
                );
            }
        } catch (Throwable $exception) {
            $this->failureLogger->log(
                channel: 'sms',
                notifiable: $notifiable,
                notification: $notification,
                exception: $exception,
                recipient: $recipients,
            );
        }
    }
}
