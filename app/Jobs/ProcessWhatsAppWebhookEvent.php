<?php

namespace App\Jobs;

use App\Models\WhatsAppWebhookEvent;
use App\Services\WhatsApp\WhatsAppWebhookProcessor;
use RuntimeException;
use Throwable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWhatsAppWebhookEvent implements ShouldQueue
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
        public int $eventId,
    ) {}

    public function handle(
        WhatsAppWebhookProcessor $processor
    ): void {
        $event = WhatsAppWebhookEvent::find($this->eventId);

        if (!$event || $event->processing_status === 'processed') {
            return;
        }

        if (
            !is_string($event->wamid) ||
            $event->wamid === ''
        ) {
            throw new RuntimeException(
                'WhatsApp webhook event has no message ID.'
            );
        }

        $processor->process($event->payload);

        $event->update([
            'processing_status' => 'processed',
            'processed_at' => now(),
            'processing_error' => null,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        WhatsAppWebhookEvent::whereKey($this->eventId)->update([
            'processing_status' => 'failed',
            'processing_error' => $exception?->getMessage()
                ?? 'WhatsApp webhook processing failed.',
        ]);
    }
}
