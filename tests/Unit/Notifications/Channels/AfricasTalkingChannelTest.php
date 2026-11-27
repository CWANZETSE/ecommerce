<?php

namespace Tests\Unit\Notifications\Channels;

use App\Notifications\Channels\AfricasTalkingChannel;
use App\Notifications\Support\NotificationFailureLogger;
use App\Services\AfricasTalking\AfricasTalkingMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\AfricasTalking\AfricasTalkingService;
use Illuminate\Notifications\Notification;
use RuntimeException;
use Tests\TestCase;
use Throwable;

class AfricasTalkingChannelTest extends TestCase
{
    use RefreshDatabase;
    
    protected function makeChannel(
        AfricasTalkingService $service,
        NotificationFailureLogger $failureLogger,
    ): AfricasTalkingChannel {
        return new AfricasTalkingChannel(
            $service,
            $failureLogger
        );
    }

    public function test_it_sends_sms_successfully(): void
    {
        $service = $this->createMock(AfricasTalkingService::class);
        $logger = $this->createMock(NotificationFailureLogger::class);

        $notifiable = new AfricasTalkingTestNotifiable(
            '+254712345678'
        );

        $notification = new AfricasTalkingTestNotification();

        $service->expects($this->once())
            ->method('send')
            ->with(
                '+254712345678',
                'Your Bazario order has been received.',
                'BAZARIO',
                true
            )
            ->willReturn([
                'SMSMessageData' => [
                    'Recipients' => [
                        [
                            'statusCode' => 101,
                            'number' => '+254712345678',
                            'status' => 'Success',
                            'messageId' => 'ATX123456',
                        ],
                    ],
                ],
            ]);

        $logger->expects($this->never())
            ->method('log');

        $channel = $this->makeChannel($service, $logger);

        $channel->send($notifiable, $notification);
    }

    public function test_it_logs_an_individual_recipient_failure(): void
    {
        $service = $this->createMock(AfricasTalkingService::class);
        $logger = $this->createMock(NotificationFailureLogger::class);

        $notifiable = new AfricasTalkingTestNotifiable(
            '+254712345678'
        );

        $notification = new AfricasTalkingTestNotification();

        $service->expects($this->once())
            ->method('send')
            ->willReturn([
                'SMSMessageData' => [
                    'Recipients' => [
                        [
                            'statusCode' => 101,
                            'number' => '+254712345678',
                            'status' => 'Success',
                            'messageId' => 'ATX001',
                        ],
                        [
                            'statusCode' => 405,
                            'number' => '+254722345678',
                            'status' => 'Insufficient balance',
                            'messageId' => '',
                        ],
                    ],
                ],
            ]);

        $logger->expects($this->once())
            ->method('log')
            ->with(
                'sms',
                $notifiable,
                $notification,
                $this->callback(function (Throwable $exception) {
                    return $exception instanceof RuntimeException
                        && str_contains(
                            $exception->getMessage(),
                            'Status code: 405'
                        );
                }),
                '+254722345678'
            );

        $channel = $this->makeChannel($service, $logger);

        $channel->send($notifiable, $notification);
    }

    public function test_it_accepts_queued_sms_responses(): void
    {
        $service = $this->createMock(AfricasTalkingService::class);
        $logger = $this->createMock(NotificationFailureLogger::class);

        $notifiable = new AfricasTalkingTestNotifiable(
            '+254712345678'
        );

        $notification = new AfricasTalkingTestNotification();

        $service->expects($this->once())
            ->method('send')
            ->willReturn([
                'SMSMessageData' => [
                    'Recipients' => [
                        [
                            'statusCode' => 102,
                            'number' => '+254712345678',
                            'status' => 'Queued',
                            'messageId' => 'ATX002',
                        ],
                    ],
                ],
            ]);

        $logger->expects($this->never())
            ->method('log');

        $channel = $this->makeChannel($service, $logger);

        $channel->send($notifiable, $notification);
    }

    public function test_it_logs_service_exceptions(): void
    {
        $service = $this->createMock(AfricasTalkingService::class);
        $logger = $this->createMock(NotificationFailureLogger::class);

        $notifiable = new AfricasTalkingTestNotifiable(
            '+254712345678'
        );

        $notification = new AfricasTalkingTestNotification();

        $service->expects($this->once())
            ->method('send')
            ->willThrowException(
                new RuntimeException('Africa\'s Talking API unavailable.')
            );

        $logger->expects($this->once())
            ->method('log')
            ->with(
                'sms',
                $notifiable,
                $notification,
                $this->callback(fn (Throwable $exception) =>
                    $exception->getMessage()
                    === 'Africa\'s Talking API unavailable.'
                ),
                '+254712345678'
            );

        $channel = $this->makeChannel($service, $logger);

        $channel->send($notifiable, $notification);
    }

    public function test_it_logs_when_the_provider_returns_no_recipient_results(): void
    {
        $service = $this->createMock(AfricasTalkingService::class);
        $logger = $this->createMock(NotificationFailureLogger::class);

        $notifiable = new AfricasTalkingTestNotifiable(
            '+254712345678'
        );

        $notification = new AfricasTalkingTestNotification();

        $service->expects($this->once())
            ->method('send')
            ->willReturn([
                'SMSMessageData' => [
                    'Message' => 'Request processed.',
                    'Recipients' => [],
                ],
            ]);

        $logger->expects($this->once())
            ->method('log')
            ->with(
                'sms',
                $notifiable,
                $notification,
                $this->callback(fn (Throwable $exception) =>
                    str_contains(
                        $exception->getMessage(),
                        'returned no recipient results'
                    )
                ),
                '+254712345678'
            );

        $channel = $this->makeChannel($service, $logger);

        $channel->send($notifiable, $notification);
    }
}

/**
 * Test notification fixture.
 */
class AfricasTalkingTestNotification extends Notification
{
    public function toAfricasTalking(
        object $notifiable
    ): AfricasTalkingMessage {
        return AfricasTalkingMessage::create(
            'Your Bazario order has been received.'
        )->from('BAZARIO');
    }
}

/**
 * Test notifiable fixture.
 */
class AfricasTalkingTestNotifiable
{
    public function __construct(
        public string $phone
    ) {}

    public function routeNotificationForAfricasTalking(
        ?Notification $notification = null
    ): string {
        return $this->phone;
    }

    public function getKey(): string
    {
        return 'test-user-id';
    }
}
