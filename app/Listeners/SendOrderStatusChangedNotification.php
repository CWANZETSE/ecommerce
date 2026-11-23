<?php

namespace App\Listeners;

use App\Events\OrderStatusChangedEvent;
use App\Enums\OrderFulfilmentStatus;
use App\Notifications\OrderDispatchedNotification;
use App\Notifications\OrderDeliveredNotification;
use RuntimeException;

class SendOrderStatusChangedNotification
{
    public function handle(OrderStatusChangedEvent $event): void
    {
        $order = $event->order;

        $notification = match ($event->newStatus) {
            OrderFulfilmentStatus::Dispatched =>
                new OrderDispatchedNotification($order),

            OrderFulfilmentStatus::Delivered =>
                new OrderDeliveredNotification($order),

            default => null,
        };

        if (!$notification) {
            return;
        }

        // Load the registered customer.
        $order->loadMissing('user');

        $customer = $order->user;

        if (!$customer) {
            report(new RuntimeException(
                "Cannot send notification for order {$order->order_number}: customer account not found."
            ));

            return;
        }

        // Send to the registered customer.
        $customer->notify($notification);
    }
}
