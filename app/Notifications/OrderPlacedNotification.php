<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Channels\AfricasTalkingChannel;
use App\Services\AfricasTalking\AfricasTalkingMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification implements ShouldQueue
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
        $message = "Thank you for your order! Order {$this->order->order_number} "
            . "has been received for KES {$this->order->grand_total}. "
            . "Please check your phone for the M-Pesa payment prompt and enter your PIN to complete payment. "
            . "If you do not receive the prompt, please try again or contact support.";

        return AfricasTalkingMessage::create($message);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_ulid' => $this->order->ulid,
            'order_number' => $this->order->order_number,
            'sms_message' => "Order {$this->order->order_number} received. Please check your phone for the M-Pesa prompt to complete payment.",
        ];
    }
}