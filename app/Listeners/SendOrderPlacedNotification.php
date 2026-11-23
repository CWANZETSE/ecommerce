<?php

namespace App\Listeners;

use App\Events\OrderPlacedEvent;
use App\Notifications\OrderPlacedNotification;
use RuntimeException;

class SendOrderPlacedNotification
{
    public function handle(OrderPlacedEvent $event): void
    {
        $order = $event->order;

        // Load the registered customer.
        $order->loadMissing('user');

        $customer = $order->user;

        // Do not send notifications without a registered customer.
        if (!$customer) {
            report(new RuntimeException(
                "Cannot send OrderPlacedNotification for order {$order->order_number}: customer account not found."
            ));

            return;
        }

        // Send through the registered User model.
        $customer->notify(
            new OrderPlacedNotification($order)
        );
    }
}
