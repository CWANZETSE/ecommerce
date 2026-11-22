<?php

namespace App\Notifications\Concerns;

use App\Notifications\Support\NotificationChannelSettings;

trait HasConfigurableChannels
{
    public function via(object $notifiable): array
    {
        // Checks administrator settings and selects enabled channels.
        return app(NotificationChannelSettings::class)
            ->channelsFor($this);
    }
}
