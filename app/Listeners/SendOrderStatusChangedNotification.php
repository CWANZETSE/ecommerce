<?php

namespace App\Listeners;

use App\Events\OrderStatusChangedEvent;
use App\Enums\OrderFulfilmentStatus;
use App\Notifications\OrderDispatchedNotification;
use App\Notifications\OrderDeliveredNotification;
use Illuminate\Support\Facades\Notification;

class SendOrderStatusChangedNotification {
    public function handle(OrderStatusChangedEvent $event): void {
        $order = $event->order;
        
        $notification = match ($event->newStatus) {
            OrderFulfilmentStatus::Dispatched => new OrderDispatchedNotification($order),
            OrderFulfilmentStatus::Delivered => new OrderDeliveredNotification($order),
            default => null,
        };

        if ($notification) {
            Notification::route('mail', $order->customer_email)->notify($notification);
        }
    }
}