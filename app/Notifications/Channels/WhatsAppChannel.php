<?php

namespace App\Notifications\Channels;

use App\Models\WhatsAppMessageLog;
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
        $messageLog = null;

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

            if (!method_exists($notifiable, 'getKey')) {
                throw new RuntimeException(
                    'The notifiable model must provide a primary key.'
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

            /*
             * Step 1: Record the outgoing message attempt.
             */
            $messageLog = WhatsAppMessageLog::create([
                'notifiable_type' => get_class($notifiable),
                'notifiable_id' => (string) $notifiable->getKey(),

                'notification_id' => $notification->id ?? null,
                'notification_type' => get_class($notification),

                'recipient' => $recipient,

                'message_type' => $message->getType(),
                'template_name' => $message->getTemplateName(),

                'status' => 'pending',
            ]);

            /*
             * Step 2: Send the message through Meta.
             */
            $response = $this->service->send(
                recipient: $recipient,
                message: $message,
            );

            /*
             * Step 3: Extract Meta's WhatsApp message ID.
             */
            $wamid = data_get($response, 'messages.0.id');

            if (!is_string($wamid) || $wamid === '') {
                throw new RuntimeException(
                    'WhatsApp API did not return a valid message ID.'
                );
            }

            /*
             * Step 4: Mark the message as accepted by Meta.
             */
            $messageLog->update([
                'wamid' => $wamid,
                'status' => 'sent',
                'provider_response' => $response,
                'sent_at' => now(),
            ]);
        } catch (Throwable $exception) {
            /*
             * Record the sending failure when a tracking record exists.
             */
            if ($messageLog !== null) {
                try {
                    $messageLog->update([
                        'status' => 'failed',
                        'error_code' => $exception->getCode() !== 0
                            ? (string) $exception->getCode()
                            : null,
                        'error_message' => $exception->getMessage(),
                        'failed_at' => now(),
                    ]);
                } catch (Throwable $loggingException) {
                    report($loggingException);
                }
            }

            /*
             * Preserve your existing centralized failure logging.
             */
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
