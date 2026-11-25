<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsAppMessageLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WhatsAppWebhookProcessor
{
    public function process(array $payload): void
    {
        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $statuses = $change['value']['statuses'] ?? [];

                foreach ($statuses as $statusData) {
                    if (is_array($statusData)) {
                        $this->processStatus($statusData);
                    }
                }
            }
        }
    }

    private function processStatus(array $statusData): void
    {
        $wamid = $statusData['id'] ?? null;
        $incomingStatus = $statusData['status'] ?? null;

        if (
            !is_string($wamid) ||
            $wamid === '' ||
            !in_array($incomingStatus, [
                'sent',
                'delivered',
                'read',
                'failed',
            ], true)
        ) {
            return;
        }

        DB::transaction(function () use (
            $wamid,
            $incomingStatus,
            $statusData
        ): void {
            $message = WhatsAppMessageLog::where('wamid', $wamid)
                ->lockForUpdate()
                ->first();

            if (!$message) {
                throw new RuntimeException(
                    "WhatsApp message {$wamid} was not found. Webhook processing will be retried."
                );
            }

            $timestamp = $this->getEventTimestamp($statusData);

            $updates = [];

            /*
             * Only update the current status when the incoming
             * event represents a valid forward transition.
             */
            if ($this->shouldUpdateStatus(
                $message->status,
                $incomingStatus
            )) {
                $updates['status'] = $incomingStatus;
            }

            /*
             * Preserve the first recorded occurrence of each
             * delivery event. Duplicate webhooks won't overwrite it.
             */
            if (
                $incomingStatus === 'sent' &&
                !$message->sent_at
            ) {
                $updates['sent_at'] = $timestamp;
            }

            if (
                $incomingStatus === 'delivered' &&
                !$message->delivered_at
            ) {
                $updates['delivered_at'] = $timestamp;
            }

            if (
                $incomingStatus === 'read' &&
                !$message->read_at
            ) {
                $updates['read_at'] = $timestamp;
            }

            if ($incomingStatus === 'failed') {
                $error = $statusData['errors'][0] ?? [];

                $updates['error_code'] = isset($error['code'])
                    ? (string) $error['code']
                    : null;

                $updates['error_message'] =
                    $error['error_data']['details']
                    ?? $error['message']
                    ?? $error['title']
                    ?? 'WhatsApp message delivery failed.';

                if (!$message->failed_at) {
                    $updates['failed_at'] = $timestamp;
                }
            }

            if ($updates !== []) {
                $message->update($updates);
            }
        });
    }

    private function shouldUpdateStatus(
        ?string $currentStatus,
        string $incomingStatus
    ): bool {
        /*
         * A read message is terminal.
         */
        if ($currentStatus === 'read') {
            return false;
        }

        /*
         * A failed message remains failed.
         * Duplicate failed events can still refresh error details.
         */
        if ($currentStatus === 'failed') {
            return false;
        }

        /*
         * A delayed failure event must not override
         * a successful delivery confirmation.
         */
        if ($currentStatus === 'delivered') {
            return $incomingStatus === 'read';
        }

        /*
         * Failure can replace pending or sent.
         */
        if ($incomingStatus === 'failed') {
            return in_array($currentStatus, [
                'pending',
                'sent',
                null,
            ], true);
        }

        $priority = [
            'pending' => 0,
            'sent' => 1,
            'delivered' => 2,
            'read' => 3,
        ];

        return ($priority[$incomingStatus] ?? -1)
            > ($priority[$currentStatus] ?? -1);
    }

    private function getEventTimestamp(array $statusData): Carbon
    {
        $timestamp = $statusData['timestamp'] ?? null;

        if (
            is_string($timestamp) &&
            ctype_digit($timestamp)
        ) {
            return Carbon::createFromTimestampUTC((int) $timestamp);
        }

        return now();
    }
}
