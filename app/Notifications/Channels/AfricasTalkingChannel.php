<?php

namespace App\Notifications\Channels;

use App\Models\SmsMessageLog;
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
        $messageLogs = [];

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

            if (!method_exists($notifiable, 'getKey')) {
                throw new RuntimeException(
                    'The notifiable model must provide a primary key.'
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

            $recipients = is_array($recipients)
                ? $recipients
                : [$recipients];

            $recipients = array_values(array_unique(array_filter(
                array_map(
                    fn ($recipient) => is_string($recipient)
                        ? trim($recipient)
                        : '',
                    $recipients
                )
            )));

            if (empty($recipients)) {
                throw new RuntimeException(
                    'No Africa\'s Talking recipient was configured.'
                );
            }

            /*
             * Step 1: Create one pending record per recipient.
             */
            foreach ($recipients as $recipient) {
                $messageLogs[$this->normalizePhone($recipient)] =
                    SmsMessageLog::create([
                        'notifiable_type' => $notifiable->getMorphClass(),
                        'notifiable_id' => (string) $notifiable->getKey(),

                        'notification_id' => $notification->id ?? null,
                        'notification_type' => get_class($notification),

                        'recipient' => $recipient,
                        'status' => 'pending',
                    ]);
            }

            /*
             * Step 2: Send the SMS through Africa's Talking.
             */
            $result = $this->service->send(
                recipients: $recipients,
                message: $message->content,
                senderId: $message->from,
                enqueue: $message->enqueue,
            );

            /*
             * Step 3: Extract individual recipient results.
             */
            $recipientResults = data_get(
                $result,
                'SMSMessageData.Recipients'
            );

            if (!is_array($recipientResults) || empty($recipientResults)) {
                throw new RuntimeException(
                    'Africa\'s Talking returned no recipient results.'
                );
            }

            $processedRecipients = [];

            foreach ($recipientResults as $recipientResult) {
                $recipientNumber = $recipientResult['number'] ?? null;

                $normalizedNumber = is_string($recipientNumber)
                    ? $this->normalizePhone($recipientNumber)
                    : null;

                $messageLog = $normalizedNumber
                    ? ($messageLogs[$normalizedNumber] ?? null)
                    : null;

                $statusCode = (int) ($recipientResult['statusCode'] ?? 0);

                $messageId = $recipientResult['messageId'] ?? null;

                $accepted = in_array(
                    $statusCode,
                    [100, 101, 102],
                    true
                );

                $messageLogData = [
                    'message_id' => is_string($messageId) && $messageId !== ''
                        ? $messageId
                        : null,

                    'status_code' => $statusCode,
                    'cost' => $recipientResult['cost'] ?? null,
                    'provider_response' => $recipientResult,
                    'status' => $accepted
                        ? $this->mapResponseStatus(
                            $statusCode,
                            $recipientResult['status'] ?? null
                        )
                        : 'failed',
                ];

                if ($statusCode === 101) {
    $messageLogData['sent_at'] = now();
}

                if (!$accepted) {
                    $messageLogData['failure_reason'] =
                        sprintf(
                            'Africa\'s Talking SMS failed. Status code: %d. Status: %s.',
                            $statusCode,
                            $recipientResult['status'] ?? 'Unknown'
                        );

                    $messageLogData['failed_at'] = now();
                }

                if ($messageLog) {
                    $messageLog->update($messageLogData);
                    $processedRecipients[] = $normalizedNumber;
                } else {
                    /*
                     * Preserve an unexpected provider result rather than
                     * silently losing its message ID.
                     */
                    $messageLog = SmsMessageLog::create([
                        'notifiable_type' => $notifiable->getMorphClass(),
                        'notifiable_id' => (string) $notifiable->getKey(),
                        'notification_id' => $notification->id ?? null,
                        'notification_type' => get_class($notification),
                        'recipient' => is_string($recipientNumber)
                            ? $recipientNumber
                            : 'Unknown',
                        ...$messageLogData,
                    ]);
                }

                if (!$accepted) {
                    $this->failureLogger->log(
                        channel: 'sms',
                        notifiable: $notifiable,
                        notification: $notification,
                        exception: new RuntimeException(
                            $messageLogData['failure_reason']
                        ),
                        recipient: $recipientNumber ?? 'Unknown',
                    );
                }
            }

            /*
             * Step 4: Mark requested recipients with no provider result.
             */
            foreach ($messageLogs as $normalizedNumber => $messageLog) {
                if (in_array($normalizedNumber, $processedRecipients, true)) {
                    continue;
                }

                if ($messageLog->status === 'pending') {
                    $messageLog->update([
                        'status' => 'failed',
                        'failure_reason' =>
                            'Africa\'s Talking did not return a result for this recipient.',
                        'failed_at' => now(),
                    ]);

                    $this->failureLogger->log(
                        channel: 'sms',
                        notifiable: $notifiable,
                        notification: $notification,
                        exception: new RuntimeException(
                            'Africa\'s Talking did not return a result for recipient '
                            . $messageLog->recipient
                        ),
                        recipient: $messageLog->recipient,
                    );
                }
            }
        } catch (Throwable $exception) {
            /*
             * Update any pending records if the sending request failed.
             */
            foreach ($messageLogs as $messageLog) {
                if ($messageLog->status === 'pending') {
                    try {
                        $messageLog->update([
                            'status' => 'failed',
                            'failure_reason' => $exception->getMessage(),
                            'failed_at' => now(),
                        ]);
                    } catch (Throwable $loggingException) {
                        report($loggingException);
                    }
                }
            }

            $this->failureLogger->log(
                channel: 'sms',
                notifiable: $notifiable,
                notification: $notification,
                exception: $exception,
                recipient: $recipients,
            );
        }
    }

    protected function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone);
    }

    protected function mapResponseStatus(
        int $statusCode,
        mixed $providerStatus
    ): string {
        return match ($statusCode) {
            100 => 'processed',
            101 => 'sent',
            102 => 'queued',
            default => is_string($providerStatus)
                ? strtolower(str_replace(' ', '_', $providerStatus))
                : 'pending',
        };
    }
}