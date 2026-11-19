<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderDispatchedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Order Dispatched — {$this->order->order_number}")
            ->greeting("Hi {$this->order->customer_name},")
            ->line("Good news! Your order has been dispatched and is on its way to you.")
            ->line("Delivery Address: {$this->order->shipping_address['county']}, {$this->order->shipping_address['address']}")
            ->line("You can track your order status from your account dashboard.");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_ulid' => $this->order->ulid,
            'order_number' => $this->order->order_number,
            'sms_message' => "Good news! Your order {$this->order->order_number} has been dispatched and is on its way.",
        ];
    }
}