<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProcessAfricasTalkingDeliveryReport;
use App\Models\SmsDeliveryReport;
use App\Models\SmsMessageLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ProcessAfricasTalkingDeliveryReportTest extends TestCase
{
    use RefreshDatabase;

    protected function createSmsMessage(
        string $messageId = 'AT-message-001',
        string $status = 'sent',
    ): SmsMessageLog {
        return SmsMessageLog::create([
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => 'user-001',
            'notification_id' => 'notification-001',
            'notification_type' => 'App\\Notifications\\OrderPlacedNotification',
            'recipient' => '254712345678',
            'message_id' => $messageId,
            'status' => $status,
        ]);
    }

    protected function createDeliveryReport(
        string $messageId = 'AT-message-001',
        string $status = 'Success',
        array $overrides = [],
    ): SmsDeliveryReport {
        $payload = [
            'id' => $messageId,
            'status' => $status,
            'phoneNumber' => '254712345678',
            'networkCode' => '63902',
            'failureReason' => null,
            'retryCount' => 0,
        ];

        return SmsDeliveryReport::create(array_merge([
            'event_key' => hash('sha256', json_encode($payload)),
            'message_id' => $messageId,
            'status' => $status,
            'phone_number' => '254712345678',
            'network_code' => '63902',
            'failure_reason' => null,
            'retry_count' => 0,
            'payload' => $payload,
            'processing_status' => 'pending',
        ], $overrides));
    }

    public function test_it_marks_sms_as_delivered_when_status_is_success(): void
    {
        $message = $this->createSmsMessage();
        $report = $this->createDeliveryReport(status: 'Success');

        (new ProcessAfricasTalkingDeliveryReport($report->id))->handle();

        $message->refresh();
        $report->refresh();

        $this->assertSame('success', $message->status);
        $this->assertNotNull($message->delivered_at);
        $this->assertSame('63902', $message->network_code);

        $this->assertSame('processed', $report->processing_status);
        $this->assertNotNull($report->processed_at);
    }

    public function test_it_marks_sms_as_failed_when_status_is_failed(): void
    {
        $message = $this->createSmsMessage();

        $report = $this->createDeliveryReport(
            status: 'Failed',
            overrides: [
                'failure_reason' => 'Delivery failed.',
            ],
        );

        (new ProcessAfricasTalkingDeliveryReport($report->id))->handle();

        $message->refresh();

        $this->assertSame('failed', $message->status);
        $this->assertNotNull($message->failed_at);
        $this->assertSame('Delivery failed.', $message->failure_reason);
    }

    public function test_it_marks_sms_as_rejected_when_status_is_rejected(): void
    {
        $message = $this->createSmsMessage();
        $report = $this->createDeliveryReport(status: 'Rejected');

        (new ProcessAfricasTalkingDeliveryReport($report->id))->handle();

        $message->refresh();

        $this->assertSame('rejected', $message->status);
        $this->assertNotNull($message->failed_at);
    }

    public function test_it_marks_sms_as_expired_when_status_is_expired(): void
    {
        $message = $this->createSmsMessage();
        $report = $this->createDeliveryReport(status: 'Expired');

        (new ProcessAfricasTalkingDeliveryReport($report->id))->handle();

        $message->refresh();

        $this->assertSame('expired', $message->status);
        $this->assertNotNull($message->failed_at);
    }

    public function test_it_updates_intermediate_delivery_statuses(): void
    {
        $message = $this->createSmsMessage(status: 'queued');

        $report = $this->createDeliveryReport(status: 'Submitted');

        (new ProcessAfricasTalkingDeliveryReport($report->id))->handle();

        $message->refresh();

        $this->assertSame('submitted', $message->status);
    }

    public function test_it_does_not_downgrade_a_final_success_status(): void
    {
        $message = $this->createSmsMessage(status: 'success');

        $message->update([
            'delivered_at' => now(),
        ]);

        $report = $this->createDeliveryReport(status: 'Sent');

        (new ProcessAfricasTalkingDeliveryReport($report->id))->handle();

        $message->refresh();

        $this->assertSame('success', $message->status);
        $this->assertNotNull($message->delivered_at);
    }

    public function test_it_does_not_overwrite_a_final_failed_status(): void
    {
        $message = $this->createSmsMessage(status: 'failed');

        $message->update([
            'failure_reason' => 'Original failure.',
            'failed_at' => now(),
        ]);

        $report = $this->createDeliveryReport(status: 'Buffered');

        (new ProcessAfricasTalkingDeliveryReport($report->id))->handle();

        $message->refresh();

        $this->assertSame('failed', $message->status);
        $this->assertSame('Original failure.', $message->failure_reason);
    }

    public function test_it_allows_success_after_absent_subscriber_status(): void
    {
        $message = $this->createSmsMessage(
            status: 'absent_subscriber'
        );

        $report = $this->createDeliveryReport(status: 'Success');

        (new ProcessAfricasTalkingDeliveryReport($report->id))->handle();

        $message->refresh();

        $this->assertSame('success', $message->status);
        $this->assertNotNull($message->delivered_at);
    }

    public function test_it_does_not_downgrade_absent_subscriber_to_sent(): void
    {
        $message = $this->createSmsMessage(
            status: 'absent_subscriber'
        );

        $report = $this->createDeliveryReport(status: 'Sent');

        (new ProcessAfricasTalkingDeliveryReport($report->id))->handle();

        $message->refresh();

        $this->assertSame('absent_subscriber', $message->status);
    }

    public function test_it_throws_when_the_sms_message_does_not_exist(): void
    {
        $report = $this->createDeliveryReport(
            messageId: 'AT-missing-message'
        );

        try {
            (new ProcessAfricasTalkingDeliveryReport($report->id))->handle();

            $this->fail('Expected a RuntimeException was not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString(
                'SMS message record not found',
                $exception->getMessage()
            );
        }
    }

    public function test_it_ignores_a_report_that_has_already_been_processed(): void
    {
        $message = $this->createSmsMessage(status: 'sent');

        $report = $this->createDeliveryReport(
            status: 'Success',
            overrides: [
                'processing_status' => 'processed',
                'processed_at' => now(),
            ],
        );

        (new ProcessAfricasTalkingDeliveryReport($report->id))->handle();

        $message->refresh();

        // The already-processed report must not change the message.
        $this->assertSame('sent', $message->status);
        $this->assertNull($message->delivered_at);
    }

    public function test_it_records_the_delivery_report_as_processed(): void
    {
        $this->createSmsMessage();

        $report = $this->createDeliveryReport(status: 'Success');

        (new ProcessAfricasTalkingDeliveryReport($report->id))->handle();

        $this->assertDatabaseHas('sms_delivery_reports', [
            'id' => $report->id,
            'processing_status' => 'processed',
        ]);
    }
}