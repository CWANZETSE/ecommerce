<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Channels\AfricasTalkingChannel;
use App\Services\AfricasTalking\AfricasTalkingMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderPaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return [
            AfricasTalkingChannel::class,
            'database',
        ];
    }

    public function toAfricasTalking(object $notifiable): AfricasTalkingMessage
    {
        $message = "Payment received! We have successfully received KES {$this->order->grand_total} "
            . "for order {$this->order->order_number}. Your order is now being processed for delivery.";

        return AfricasTalkingMessage::create($message);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_ulid' => $this->order->ulid,
            'order_number' => $this->order->order_number,
            'sms_message' => "Payment received! Your order {$this->order->order_number} is now being processed.",
        ];
    }
}