<?php

namespace Tests\Unit\Services;

use App\Services\AfricasTalking\AfricasTalkingService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class AfricasTalkingServiceTest extends TestCase
{
    protected AfricasTalkingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'africastalking.username' => 'sandbox',
            'africastalking.api_key' => 'test-api-key',
            'africastalking.sender_id' => 'BAZARIO',
            'africastalking.endpoint' =>
                'https://api.sandbox.africastalking.com/version1/messaging',
            'africastalking.timeout' => 15,
        ]);

        $this->service = app(AfricasTalkingService::class);

        Http::preventStrayRequests();
    }

    public function test_it_sends_sms_using_the_sandbox_endpoint(): void
    {
        $endpoint = config('africastalking.endpoint');

        Http::fake([
            $endpoint => Http::response([
                'SMSMessageData' => [
                    'Message' => 'Sent to 1/1 Total cost KES 0.80',
                    'Recipients' => [
                        [
                            'statusCode' => 101,
                            'number' => '+254712345678',
                            'cost' => 'KES 0.80',
                            'status' => 'Success',
                            'messageId' => 'ATX123456',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->send(
            recipients: '+254712345678',
            message: 'Your Bazario order has been received.',
        );

        $this->assertArrayHasKey('SMSMessageData', $result);

        Http::assertSent(function (Request $request) use ($endpoint) {
            return $request->url() === $endpoint
                && $request->method() === 'POST'
                && $request->hasHeader('Apikey', 'test-api-key')
                && $request->hasHeader('Accept', 'application/json')
                && $request->data() === [
                    'username' => 'sandbox',
                    'phoneNumbers' => '+254712345678',
                    'message' => 'Your Bazario order has been received.',
                    'senderId' => 'BAZARIO',
                    'enqueue' => 1,
                ];
        });
    }

    public function test_it_uses_the_live_bulk_sms_endpoint_when_configured(): void
    {
        $endpoint = 'https://api.africastalking.com/version1/messaging/bulk';

        config([
            'africastalking.endpoint' => $endpoint,
        ]);

        Http::fake([
            $endpoint => Http::response([
                'SMSMessageData' => [
                    'Message' => 'Sent to 1/1',
                    'Recipients' => [
                        [
                            'statusCode' => 101,
                            'number' => '+254712345678',
                            'cost' => 'KES 0.80',
                            'status' => 'Success',
                            'messageId' => 'ATX123456',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->service->send(
            recipients: '+254712345678',
            message: 'Test live endpoint.',
        );

        Http::assertSent(
            fn (Request $request) => $request->url() === $endpoint
        );
    }

    public function test_it_supports_multiple_recipients(): void
    {
        $endpoint = config('africastalking.endpoint');

        Http::fake([
            $endpoint => Http::response([
                'SMSMessageData' => [
                    'Message' => 'Sent to 2/2',
                    'Recipients' => [
                        [
                            'statusCode' => 101,
                            'number' => '+254712345678',
                            'status' => 'Success',
                            'messageId' => 'ATX001',
                        ],
                        [
                            'statusCode' => 102,
                            'number' => '+254722345678',
                            'status' => 'Queued',
                            'messageId' => 'ATX002',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->service->send(
            recipients: [
                '+254712345678',
                '+254722345678',
            ],
            message: 'Bazario notification.',
        );

        Http::assertSent(function (Request $request) {
            return $request->data()['phoneNumbers']
                === '+254712345678,+254722345678';
        });
    }

    public function test_it_throws_an_exception_when_no_recipients_are_provided(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'At least one SMS recipient is required.'
        );

        $this->service->send(
            recipients: [],
            message: 'Test message.',
        );
    }

    public function test_it_throws_an_exception_when_username_is_missing(): void
    {
        config(['africastalking.username' => null]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Africa\'s Talking username is not configured.'
        );

        $this->service->send(
            recipients: '+254712345678',
            message: 'Test message.',
        );
    }

    public function test_it_throws_an_exception_when_api_key_is_missing(): void
    {
        config(['africastalking.api_key' => null]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Africa\'s Talking API key is not configured.'
        );

        $this->service->send(
            recipients: '+254712345678',
            message: 'Test message.',
        );
    }

    public function test_it_throws_an_exception_when_sender_id_is_missing(): void
    {
        config(['africastalking.sender_id' => null]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Africa\'s Talking sender ID is not configured.'
        );

        $this->service->send(
            recipients: '+254712345678',
            message: 'Test message.',
        );
    }

    public function test_it_throws_an_exception_when_endpoint_is_missing(): void
    {
        config(['africastalking.endpoint' => null]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Africa\'s Talking SMS endpoint is not configured.'
        );

        $this->service->send(
            recipients: '+254712345678',
            message: 'Test message.',
        );
    }

    public function test_it_throws_an_exception_when_the_api_returns_an_http_error(): void
    {
        Http::fake([
            '*' => Http::response([
                'errorMessage' => 'Invalid API credentials.',
            ], 401),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Africa\'s Talking SMS request failed:'
        );

        $this->service->send(
            recipients: '+254712345678',
            message: 'Test message.',
        );
    }

    public function test_it_throws_an_exception_when_the_api_response_is_invalid(): void
    {
        Http::fake([
            '*' => Http::response([
                'unexpected' => 'Invalid response',
            ], 200),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Invalid response received from Africa\'s Talking:'
        );

        $this->service->send(
            recipients: '+254712345678',
            message: 'Test message.',
        );
    }

    public function test_it_can_disable_enqueue(): void
    {
        $endpoint = config('africastalking.endpoint');

        Http::fake([
            $endpoint => Http::response([
                'SMSMessageData' => [
                    'Message' => 'Processed',
                    'Recipients' => [],
                ],
            ], 200),
        ]);

        // This test verifies the request payload.
        // The response validation is intentionally not the subject here.
        try {
            $this->service->send(
                recipients: '+254712345678',
                message: 'Immediate SMS.',
                enqueue: false,
            );
        } catch (RuntimeException) {
            // An empty recipient response is rejected by the service.
        }

        Http::assertSent(function (Request $request) {
            return $request->data()['enqueue'] === 0;
        });
    }
}
