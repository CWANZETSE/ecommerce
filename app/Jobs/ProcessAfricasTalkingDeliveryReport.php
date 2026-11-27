<?php

namespace App\Jobs;

use App\Models\SmsDeliveryReport;
use App\Models\SmsMessageLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

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
        DB::transaction(function (): void {
            $report = SmsDeliveryReport::query()
                ->lockForUpdate()
                ->find($this->reportId);

            if (
                !$report ||
                $report->processing_status === 'processed'
            ) {
                return;
            }

            $message = SmsMessageLog::query()
                ->where('message_id', $report->message_id)
                ->lockForUpdate()
                ->first();

            /*
             * A delivery callback can arrive before the outgoing
             * message record has received its provider message ID.
             *
             * Throwing allows Laravel's queue retry mechanism to retry.
             */
            if (!$message) {
                throw new RuntimeException(
                    'SMS message record not found for provider message ID: '
                    . $report->message_id
                );
            }

            $incomingStatus = $this->normalizeStatus($report->status);

            /*
             * Unknown provider statuses are retained in the callback
             * table but do not alter the outgoing message.
             */
            if ($incomingStatus === null) {
                $report->update([
                    'processing_status' => 'processed',
                    'processed_at' => now(),
                    'processing_error' => null,
                ]);

                return;
            }

            $finalStatuses = [
                'success',
                'rejected',
                'failed',
                'expired',
            ];

            $currentIsFinal = in_array(
                $message->status,
                $finalStatuses,
                true
            );

            $incomingIsFinal = in_array(
                $incomingStatus,
                $finalStatuses,
                true
            );

            /*
             * Never replace an already-final delivery result.
             *
             * A final callback can replace an intermediate status.
             * Intermediate callbacks must follow status progression.
             */
            if (!$currentIsFinal) {
                $currentRank = $this->statusRank($message->status);
                $incomingRank = $this->statusRank($incomingStatus);

                if (
                    $incomingIsFinal ||
                    $incomingRank >= $currentRank
                ) {
                    $updates = [
                        'status' => $incomingStatus,
                    ];

                    if ($report->network_code !== null) {
                        $updates['network_code'] = $report->network_code;
                    }

                    if ($report->failure_reason !== null) {
                        $updates['failure_reason'] = $report->failure_reason;
                    }

                    if (
                        in_array($incomingStatus, ['sent', 'success'], true)
                        && !$message->sent_at
                    ) {
                        $updates['sent_at'] = now();
                    }

                    if ($incomingStatus === 'success') {
                        $updates['delivered_at'] =
                            $message->delivered_at ?? now();

                        $updates['failure_reason'] = null;
                    }

                    if (
                        in_array(
                            $incomingStatus,
                            ['rejected', 'failed', 'expired'],
                            true
                        )
                    ) {
                        $updates['failed_at'] =
                            $message->failed_at ?? now();
                    }

                    $message->update($updates);
                }
            }

            $report->update([
                'processing_status' => 'processed',
                'processed_at' => now(),
                'processing_error' => null,
            ]);
        }, attempts: 3);
    }

    protected function normalizeStatus(string $status): ?string
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
            default => null,
        };
    }

    protected function statusRank(string $status): int
{
    return match ($status) {
        'pending', 'queued', 'processed' => 0,
        'buffered' => 1,
        'submitted' => 2,
        'absent_subscriber' => 3,
        'sent' => 4,
        default => -1,
    };
}

    public function failed(Throwable $exception): void
    {
        SmsDeliveryReport::query()
            ->whereKey($this->reportId)
            ->where('processing_status', '!=', 'processed')
            ->update([
                'processing_status' => 'failed',
                'processing_error' => $exception->getMessage(),
            ]);
    }
}
