<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderDeliveredNotification extends Notification implements ShouldQueue
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
            ->subject("Order Delivered — {$this->order->order_number}")
            ->greeting("Hi {$this->order->customer_name},")
            ->line("Your order has been successfully delivered.")
            ->line("We hope you love your purchase! Please take a moment to leave a review on the products you bought.")
            ->action('Leave a Review', url("/customer/orders/{$this->order->ulid}"));
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