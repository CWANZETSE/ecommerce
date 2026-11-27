<?php

namespace App\Jobs;

use App\Models\SmsDeliveryReport;
use App\Models\SmsMessageLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

class ProcessAfricasTalkingDeliveryReport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;

    public function backoff(): array
    {
        return [5, 15, 30, 60, 120];
    }

    public function __construct(
        public int $reportId,
    ) {}

    public function handle(): void
    {
        $report = SmsDeliveryReport::find($this->reportId);

        if (!$report || $report->processing_status === 'processed') {
            return;
        }

        $message = SmsMessageLog::where(
            'message_id',
            $report->message_id
        )->first();

        /*
         * The callback may arrive before the outgoing message record
         * has been updated with the provider message ID.
         *
         * Throwing allows the queue to retry this job.
         */
        if (!$message) {
            throw new RuntimeException(
                'SMS message record not found for provider message ID: '
                . $report->message_id
            );
        }

        $incomingStatus = $this->normalizeStatus($report->status);

        $finalStatuses = [
            'success',
            'rejected',
            'failed',
            'expired',
        ];

        $currentStatus = $message->status;

        $currentIsFinal = in_array(
            $currentStatus,
            $finalStatuses,
            true
        );

        $incomingIsFinal = in_array(
            $incomingStatus,
            $finalStatuses,
            true
        );

        /*
         * Do not allow a late intermediate callback to overwrite
         * a final delivery result.
         */
        if (!$currentIsFinal) {
            if (
                $incomingIsFinal ||
                $this->statusRank($incomingStatus) >=
                    $this->statusRank($currentStatus)
            ) {
                $updates = [
                    'status' => $incomingStatus,
                    'network_code' => $report->network_code,
                ];

                if ($report->failure_reason) {
                    $updates['failure_reason'] = $report->failure_reason;
                }

                if (
                    in_array(
                        $incomingStatus,
                        ['sent', 'success'],
                        true
                    ) && !$message->sent_at
                ) {
                    $updates['sent_at'] = now();
                }

                if ($incomingStatus === 'success') {
                    $updates['delivered_at'] = now();
                    $updates['failure_reason'] = null;
                }

                if (
                    in_array(
                        $incomingStatus,
                        ['rejected', 'failed', 'expired'],
                        true
                    )
                ) {
                    $updates['failed_at'] = now();
                }

                $message->update($updates);
            }
        }

        $report->update([
            'processing_status' => 'processed',
            'processed_at' => now(),
            'processing_error' => null,
        ]);
    }

    protected function normalizeStatus(string $status): string
    {
        return match (strtolower(trim($status))) {
            'sent' => 'sent',
            'submitted' => 'submitted',
            'buffered' => 'buffered',
            'success' => 'success',
            'rejected' => 'rejected',
            'failed' => 'failed',
            'absentsubscriber' => 'absent_subscriber',
            'expired' => 'expired',
            default => 'unknown',
        };
    }

    protected function statusRank(string $status): int
    {
        return match ($status) {
            'pending', 'queued', 'processed' => 0,
            'buffered' => 1,
            'submitted' => 2,
            'sent' => 3,
            'absent_subscriber' => 4,
            default => -1,
        };
    }

    public function failed(\Throwable $exception): void
    {
        SmsDeliveryReport::whereKey($this->reportId)
            ->where('processing_status', '!=', 'processed')
            ->update([
                'processing_status' => 'failed',
                'processing_error' => $exception->getMessage(),
            ]);
    }
}