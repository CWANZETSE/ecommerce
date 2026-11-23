<?php

namespace App\Listeners;

use App\Events\OrderPaidEvent;
use App\Notifications\OrderPaidNotification;
use RuntimeException;

class SendOrderPaidNotification
{
    public function handle(OrderPaidEvent $event): void
    {
        $order = $event->order;

        // Load the registered customer.
        $order->loadMissing('user');

        $customer = $order->user;

        // Do not send notifications without a registered customer.
        if (!$customer) {
            report(new RuntimeException(
                "Cannot send OrderPaidNotification for order {$order->order_number}: customer account not found."
            ));

            return;
        }

        // Send through the registered User model.
        $customer->notify(
            new OrderPaidNotification($order)
        );
    }
}
