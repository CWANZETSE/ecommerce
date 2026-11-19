<?php

namespace App\Listeners;
use App\Events\PaymentFailedEvent;
use App\Notifications\PaymentFailedNotification;
use Illuminate\Support\Facades\Notification;

class SendPaymentFailedNotification {
    public function handle(PaymentFailedEvent $event): void {
        Notification::route('mail', $event->order->customer_email)
            ->notify(new PaymentFailedNotification($event->order, $event->reason));
    }
}