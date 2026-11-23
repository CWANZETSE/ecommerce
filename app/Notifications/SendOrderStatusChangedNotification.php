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

        // Load the registered customer associated with this order.
        $order->loadMissing('user');

        $customer = $order->user;

        // Guest checkout is not supported, so every order must
        // have an associated registered customer.
        if (!$customer) {
            report(new RuntimeException(
                "Cannot send order notification: Order {$order->order_number} has no associated customer."
            ));

            return;
        }

        // Send through the customer's registered User model.
        // Laravel will resolve email, SMS and WhatsApp routes
        // using the User model's notification routing methods.
        $customer->notify($notification);
    }
}
