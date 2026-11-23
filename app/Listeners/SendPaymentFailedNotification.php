<?php

namespace App\Listeners;

use App\Events\PaymentFailedEvent;
use App\Notifications\PaymentFailedNotification;
use RuntimeException;

class SendPaymentFailedNotification
{
    public function handle(PaymentFailedEvent $event): void
    {
        $order = $event->order;

        $order->loadMissing('user');

        $customer = $order->user;

        if (!$customer) {
            report(new RuntimeException(
                "Cannot send PaymentFailedNotification for order {$order->order_number}: customer account not found."
            ));

            return;
        }

        $customer->notify(
            new PaymentFailedNotification($order, $event->reason)
        );
    }
}
