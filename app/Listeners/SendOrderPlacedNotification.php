<?php
namespace App\Listeners;
use App\Events\OrderPlacedEvent;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Support\Facades\Notification;

class SendOrderPlacedNotification {
    public function handle(OrderPlacedEvent $event): void {
        Notification::route('mail', $event->order->customer_email)
            ->notify(new OrderPlacedNotification($event->order));
    }
}