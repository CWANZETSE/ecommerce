<?php

namespace App\Listeners;

use App\Enums\UserRole;
use App\Events\OrderPlacedEvent;
use App\Models\User;
use App\Notifications\AdminOrderPlacedNotification;

class SendAdminOrderPlacedNotification
{
    public function handle(OrderPlacedEvent $event): void
    {
        User::query()
            ->where('role', UserRole::Admin->value)
            ->each(function (User $admin) use ($event): void {
                $admin->notify(
                    new AdminOrderPlacedNotification($event->order)
                );
            });
    }
}
