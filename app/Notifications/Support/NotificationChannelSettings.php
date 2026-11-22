<?php

namespace App\Notifications\Support;

use App\Notifications\Channels\AfricasTalkingChannel;
use App\Notifications\Channels\MailNotificationChannel;
use App\Notifications\Channels\WhatsAppChannel;
use App\Models\StoreSetting;
use Illuminate\Notifications\Notification;

class NotificationChannelSettings
{
    public function channelsFor(Notification $notification): array
    {
        $channels = [];

        $availableChannels = [
            'notification_email_enabled' => [
                'method' => 'toMail',
                'channel' => MailNotificationChannel::class,
            ],
            'notification_sms_enabled' => [
                'method' => 'toAfricasTalking',
                'channel' => AfricasTalkingChannel::class,
            ],
            'notification_whatsapp_enabled' => [
                'method' => 'toWhatsApp',
                'channel' => WhatsAppChannel::class,
            ],
        ];

        foreach ($availableChannels as $setting => $configuration) {
            if (
                $this->isEnabled($setting)
                && method_exists($notification, $configuration['method'])
            ) {
                $channels[] = $configuration['channel'];
            }
        }

        // Database notifications are always enabled.
        $channels[] = 'database';

        return $channels;
    }

    private function isEnabled(string $key): bool
    {
        return filter_var(
            StoreSetting::get($key, 'false'),
            FILTER_VALIDATE_BOOLEAN
        );
    }
}
