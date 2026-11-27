<?php

namespace Tests\Feature\Webhooks;

use App\Jobs\ProcessAfricasTalkingDeliveryReport;
use App\Models\SmsDeliveryReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AfricasTalkingDeliveryReportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function callbackPayload(): array
    {
        return [
            'id' => 'AT-TEST-MESSAGE-001',
            'status' => 'Success',
            'phoneNumber' => '+254712345678',
            'networkCode' => '63902',
            'failureReason' => '',
            'retryCount' => '0',
        ];
    }

    protected function sendCallback(array $payload = [])
    {
        return $this->post(
            route('webhooks.africastalking.delivery-reports'),
            array_merge($this->callbackPayload(), $payload),
            [
                'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
                'HTTP_ACCEPT' => 'text/plain',
            ]
        );
    }

    public function test_it_accepts_and_persists_a_delivery_report(): void
    {
        Queue::fake();

        $response = $this->sendCallback();

        $response->assertOk();
        $response->assertSeeText('Received');

        $this->assertDatabaseHas('sms_delivery_reports', [
            'message_id' => 'AT-TEST-MESSAGE-001',
            'status' => 'Success',
            'phone_number' => '+254712345678',
            'network_code' => '63902',
            'processing_status' => 'pending',
        ]);

        Queue::assertPushed(
            ProcessAfricasTalkingDeliveryReport::class,
            1
        );
    }

    public function test_it_does_not_create_duplicate_reports_for_identical_callbacks(): void
    {
        Queue::fake();

        $this->sendCallback()->assertOk();
        $this->sendCallback()->assertOk();

        $this->assertDatabaseCount('sms_delivery_reports', 1);

        Queue::assertPushed(
            ProcessAfricasTalkingDeliveryReport::class,
            1
        );
    }

    public function test_it_dispatches_a_new_job_when_an_identical_report_was_previously_failed(): void
    {
        Queue::fake();

        $payload = $this->callbackPayload();

        $this->sendCallback()->assertOk();

        $report = SmsDeliveryReport::query()->firstOrFail();

        $report->update([
            'processing_status' => 'failed',
            'processing_error' => 'Previous processing attempt failed.',
        ]);

        $this->sendCallback()->assertOk();

        $this->assertDatabaseCount('sms_delivery_reports', 1);

        $this->assertDatabaseHas('sms_delivery_reports', [
            'id' => $report->id,
            'processing_status' => 'pending',
            'processing_error' => null,
        ]);

        Queue::assertPushed(
            ProcessAfricasTalkingDeliveryReport::class,
            2
        );
    }

    public function test_it_rejects_a_callback_without_a_message_id(): void
    {
        Queue::fake();

        $response = $this->sendCallback([
            'id' => '',
        ]);

        $response->assertUnprocessable();

        $this->assertDatabaseCount('sms_delivery_reports', 0);

        Queue::assertNothingPushed();
    }

    public function test_it_rejects_a_callback_without_a_status(): void
    {
        Queue::fake();

        $response = $this->sendCallback([
            'status' => '',
        ]);

        $response->assertUnprocessable();

        $this->assertDatabaseCount('sms_delivery_reports', 0);

        Queue::assertNothingPushed();
    }

    public function test_it_accepts_optional_callback_fields(): void
    {
        Queue::fake();

        $response = $this->sendCallback([
            'networkCode' => '',
            'failureReason' => '',
            'retryCount' => '',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('sms_delivery_reports', [
            'message_id' => 'AT-TEST-MESSAGE-001',
            'status' => 'Success',
            'network_code' => null,
            'retry_count' => 0,
        ]);

        Queue::assertPushed(
            ProcessAfricasTalkingDeliveryReport::class,
            1
        );
    }
}
