<?php

namespace App\Notifications;

use App\Models\Order;
use App\Services\AfricasTalking\AfricasTalkingMessage;
use App\Notifications\Channels\AfricasTalkingChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderDeliveredNotification extends Notification implements ShouldQueue
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
        $message = "Your order {$this->order->order_number} has been successfully delivered. "
            . "Thank you for shopping with us!";

        return AfricasTalkingMessage::create($message);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_ulid' => $this->order->ulid,
            'order_number' => $this->order->order_number,
            'sms_message' => "Order {$this->order->order_number} has been delivered. Thank you for shopping with us!",
        ];
    }
}