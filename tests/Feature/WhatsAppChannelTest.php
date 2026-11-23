<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WhatsAppMessageLog;
use App\Notifications\Channels\WhatsAppChannel;
use App\Notifications\Support\NotificationFailureLogger;
use App\Services\WhatsApp\WhatsAppMessage;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class WhatsAppChannelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'whatsapp.access_token' => 'test-access-token',
            'whatsapp.phone_number_id' => '123456789',
            'whatsapp.graph_version' => 'v27.0',
            'whatsapp.base_url' => 'https://graph.facebook.com',
            'whatsapp.timeout' => 30,
        ]);
    }

    public function test_successful_message_is_recorded_as_sent(): void
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'messaging_product' => 'whatsapp',
                'contacts' => [
                    [
                        'input' => '254712345678',
                        'wa_id' => '254712345678',
                    ],
                ],
                'messages' => [
                    [
                        'id' => 'wamid.test-success-123',
                    ],
                ],
            ], 200),
        ]);

        $failureLogger = Mockery::mock(NotificationFailureLogger::class);
        $failureLogger->shouldNotReceive('log');

        $channel = new WhatsAppChannel(
            app(WhatsAppService::class),
            $failureLogger,
        );

        $user = $this->makeNotifiableUser();

        $notification = $this->makeNotification();

        $channel->send($user, $notification);

        $this->assertDatabaseHas('whatsapp_messages', [
            'notifiable_type' => User::class,
            'notifiable_id' => $user->getKey(),
            'recipient' => '254712345678',
            'wamid' => 'wamid.test-success-123',
            'message_type' => 'text',
            'status' => 'sent',
        ]);

        $log = WhatsAppMessageLog::first();

        $this->assertNotNull($log->sent_at);
        $this->assertNotNull($log->provider_response);
    }

    public function test_failed_message_is_recorded_and_failure_logger_is_called(): void
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'error' => [
                    'message' => 'Invalid access token',
                    'type' => 'OAuthException',
                    'code' => 190,
                ],
            ], 400),
        ]);

        $failureLogger = Mockery::mock(NotificationFailureLogger::class);
        $failureLogger->shouldReceive('log')->once();

        $channel = new WhatsAppChannel(
            app(WhatsAppService::class),
            $failureLogger,
        );

        $user = $this->makeNotifiableUser();

        $notification = $this->makeNotification();

        $channel->send($user, $notification);

        $this->assertDatabaseHas('whatsapp_messages', [
            'notifiable_type' => User::class,
            'notifiable_id' => $user->getKey(),
            'recipient' => '254712345678',
            'message_type' => 'text',
            'status' => 'failed',
        ]);

        $log = WhatsAppMessageLog::first();

        $this->assertNotNull($log->failed_at);
        $this->assertNotEmpty($log->error_message);
        $this->assertNull($log->wamid);
    }

    private function makeNotifiableUser(): User
    {
        $user = new User();

        $user->id = (string) Str::uuid();
        $user->phone = '254712345678';

        return $user;
    }

    private function makeNotification(): Notification
    {
        return new class extends Notification
        {
            public function toWhatsApp($notifiable): WhatsAppMessage
            {
                return WhatsAppMessage::text(
                    'This is a WhatsApp channel test.'
                );
            }
        };
    }
}
