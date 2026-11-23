<?php

namespace Tests\Feature;

use App\Models\WhatsAppMessageLog;
use App\Services\WhatsApp\WhatsAppWebhookProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppWebhookProcessorTest extends TestCase
{
    use RefreshDatabase;

    private function createMessage(
        string $status = 'sent'
    ): WhatsAppMessageLog {
        return WhatsAppMessageLog::create([
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => 'test-user-uuid',
            'notification_id' => 'test-notification-id',
            'notification_type' => 'App\Notifications\OrderPaidNotification',
            'recipient' => '254712345678',
            'wamid' => 'wamid.test-message-123',
            'message_type' => 'text',
            'status' => $status,
        ]);
    }

    private function payload(
        string $status,
        array $additionalData = []
    ): array {
        return [
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'changes' => [
                        [
                            'field' => 'messages',
                            'value' => [
                                'statuses' => [
                                    array_merge([
                                        'id' => 'wamid.test-message-123',
                                        'status' => $status,
                                        'timestamp' => '1795430000',
                                        'recipient_id' => '254712345678',
                                    ], $additionalData),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function test_delivered_status_updates_message(): void
    {
        $message = $this->createMessage();

        app(WhatsAppWebhookProcessor::class)->process(
            $this->payload('delivered')
        );

        $message->refresh();

        $this->assertSame('delivered', $message->status);
        $this->assertNotNull($message->delivered_at);
    }

    public function test_read_status_updates_message(): void
    {
        $message = $this->createMessage('delivered');

        app(WhatsAppWebhookProcessor::class)->process(
            $this->payload('read')
        );

        $message->refresh();

        $this->assertSame('read', $message->status);
        $this->assertNotNull($message->read_at);
    }

    public function test_failed_status_records_error_details(): void
    {
        $message = $this->createMessage();

        app(WhatsAppWebhookProcessor::class)->process(
            $this->payload('failed', [
                'errors' => [
                    [
                        'code' => 131026,
                        'title' => 'Message undeliverable',
                        'message' => 'Message could not be delivered.',
                    ],
                ],
            ])
        );

        $message->refresh();

        $this->assertSame('failed', $message->status);
        $this->assertSame('131026', $message->error_code);
        $this->assertSame(
            'Message could not be delivered.',
            $message->error_message
        );
        $this->assertNotNull($message->failed_at);
    }

    public function test_delayed_delivered_event_does_not_downgrade_read_status(): void
    {
        $message = $this->createMessage('read');

        app(WhatsAppWebhookProcessor::class)->process(
            $this->payload('delivered')
        );

        $this->assertSame('read', $message->fresh()->status);
    }

    public function test_unknown_message_id_is_ignored(): void
    {
        $message = $this->createMessage();

        $payload = $this->payload('delivered');

        $payload['entry'][0]['changes'][0]['value']['statuses'][0]['id']
            = 'wamid.unknown';

        app(WhatsAppWebhookProcessor::class)->process($payload);

        $this->assertSame('sent', $message->fresh()->status);
    }
}
