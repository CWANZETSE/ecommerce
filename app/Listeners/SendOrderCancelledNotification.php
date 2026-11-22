<?php

namespace App\Listeners;

use App\Events\OrderCancelledEvent;
use App\Notifications\OrderCancelledNotification;

class SendOrderCancelledNotification
{
    public function handle(OrderCancelledEvent $event): void
    {
        $user = $event->order->user;

        if (!$user) {
            return;
        }

        $user->notify(
            new OrderCancelledNotification(
                $event->order,
                $event->wasPaid
            )
        );
    }
}
