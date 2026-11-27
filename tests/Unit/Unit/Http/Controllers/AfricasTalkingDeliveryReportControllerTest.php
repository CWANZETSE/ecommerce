<?php

namespace Tests\Unit\Http\Controllers;

use App\Jobs\ProcessAfricasTalkingDeliveryReport;
use App\Models\SmsDeliveryReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AfricasTalkingDeliveryReportControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $endpoint =
        '/api/webhooks/africastalking/delivery-reports';

    public function test_it_stores_and_queues_a_delivery_report(): void
    {
        Queue::fake();

        $response = $this->post($this->endpoint, [
            'id' => 'AT-message-001',
            'status' => 'Success',
            'phoneNumber' => '254712345678',
            'networkCode' => '63902',
            'retryCount' => '0',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('sms_delivery_reports', [
            'message_id' => 'AT-message-001',
            'status' => 'Success',
            'phone_number' => '254712345678',
            'network_code' => '63902',
            'processing_status' => 'pending',
        ]);

        $report = SmsDeliveryReport::firstOrFail();

        Queue::assertPushed(
            ProcessAfricasTalkingDeliveryReport::class,
            fn ($job) => $job->reportId === $report->id
        );
    }

    public function test_it_does_not_create_duplicate_identical_reports(): void
    {
        Queue::fake();

        $payload = [
            'id' => 'AT-message-002',
            'status' => 'Success',
            'phoneNumber' => '254712345678',
            'networkCode' => '63902',
            'retryCount' => '0',
        ];

        $this->post($this->endpoint, $payload)->assertOk();
        $this->post($this->endpoint, $payload)->assertOk();

        $this->assertDatabaseCount('sms_delivery_reports', 1);

        Queue::assertPushed(
            ProcessAfricasTalkingDeliveryReport::class,
            1
        );
    }

    public function test_it_accepts_different_status_reports_for_the_same_message(): void
    {
        Queue::fake();

        $this->post($this->endpoint, [
            'id' => 'AT-message-003',
            'status' => 'Sent',
            'phoneNumber' => '254712345678',
        ])->assertOk();

        $this->post($this->endpoint, [
            'id' => 'AT-message-003',
            'status' => 'Success',
            'phoneNumber' => '254712345678',
        ])->assertOk();

        $this->assertDatabaseCount('sms_delivery_reports', 2);

        Queue::assertPushed(
            ProcessAfricasTalkingDeliveryReport::class,
            2
        );
    }

    public function test_it_rejects_a_delivery_report_without_a_message_id(): void
    {
        Queue::fake();

        $response = $this->post($this->endpoint, [
            'status' => 'Success',
            'phoneNumber' => '254712345678',
        ]);

        $response->assertUnprocessable();

        $this->assertDatabaseCount('sms_delivery_reports', 0);

        Queue::assertNothingPushed();
    }

    public function test_it_rejects_a_delivery_report_without_a_status(): void
    {
        Queue::fake();

        $response = $this->post($this->endpoint, [
            'id' => 'AT-message-004',
            'phoneNumber' => '254712345678',
        ]);

        $response->assertUnprocessable();

        $this->assertDatabaseCount('sms_delivery_reports', 0);

        Queue::assertNothingPushed();
    }
}