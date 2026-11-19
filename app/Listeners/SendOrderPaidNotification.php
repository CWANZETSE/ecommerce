<?php

namespace App\Listeners;
use App\Events\OrderPaidEvent;
use App\Notifications\OrderPaidNotification;
use Illuminate\Support\Facades\Notification;

class SendOrderPaidNotification {
    public function handle(OrderPaidEvent $event): void {
        Notification::route('mail', $event->order->customer_email)
            ->notify(new OrderPaidNotification($event->order));
    }
}