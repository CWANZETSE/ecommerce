<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsAppMessageLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookProcessor
{
    public function process(array $payload): void
    {
        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $statuses = $change['value']['statuses'] ?? [];

                foreach ($statuses as $statusData) {
                    $this->processStatus($statusData);
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

        $message = WhatsAppMessageLog::where('wamid', $wamid)->first();

        if (!$message) {
            Log::warning('WhatsApp webhook references an unknown message.', [
                'wamid' => $wamid,
                'status' => $incomingStatus,
            ]);

            return;
        }

        /*
         * A read message must never move backwards.
         * A failed message must not be changed by delayed
         * non-failure events.
         */
        if (
            $message->status === 'read' ||
            (
                $message->status === 'failed' &&
                $incomingStatus !== 'failed'
            )
        ) {
            return;
        }

        $timestamp = $this->getEventTimestamp($statusData);

        $updates = [];

        /*
         * Update the current delivery status without allowing
         * delayed events to move it backwards.
         */
        if ($this->shouldUpdateStatus(
            $message->status,
            $incomingStatus
        )) {
            $updates['status'] = $incomingStatus;
        }

        /*
         * Preserve the actual event timestamps.
         */
        if ($incomingStatus === 'sent') {
            $updates['sent_at'] = $timestamp;
        }

        if ($incomingStatus === 'delivered') {
            $updates['delivered_at'] = $timestamp;
        }

        if ($incomingStatus === 'read') {
            $updates['read_at'] = $timestamp;
        }

        if ($incomingStatus === 'failed') {
            $updates['failed_at'] = $timestamp;

            $error = $statusData['errors'][0] ?? [];

            $updates['error_code'] = isset($error['code'])
                ? (string) $error['code']
                : null;

            $updates['error_message'] =
                $error['error_data']['details']
                ?? $error['message']
                ?? $error['title']
                ?? 'WhatsApp message delivery failed.';
        }

        if ($updates !== []) {
            $message->update($updates);
        }
    }

    private function shouldUpdateStatus(
        string $currentStatus,
        string $incomingStatus,
    ): bool {
        $priority = [
            'pending' => 0,
            'sent' => 1,
            'delivered' => 2,
            'read' => 3,
        ];

        if ($incomingStatus === 'failed') {
            return $currentStatus !== 'read';
        }

        if ($currentStatus === 'failed') {
            return false;
        }

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
