<?php

namespace App\Listeners;
use App\Events\OrderCancelledEvent;
use App\Notifications\OrderCancelledNotification;
use Illuminate\Support\Facades\Notification;

class SendOrderCancelledNotification {
    public function handle(OrderCancelledEvent $event): void {
        Notification::route('mail', $event->order->customer_email)
            ->notify(new OrderCancelledNotification($event->order, $event->wasPaid));
    }
}