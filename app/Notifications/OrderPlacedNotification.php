<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification implements ShouldQueue
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
            ->subject("Order Received — {$this->order->order_number}")
            ->greeting("Hi {$this->order->customer_name},")
            ->line("Thank you for your order! We have received your order for KES {$this->order->grand_total}.")
            ->line("We have sent an M-Pesa payment prompt to your phone. Please enter your PIN to complete the payment.")
            ->action('View Order', url("/customer/orders/{$this->order->ulid}"))
            ->line("If you do not receive the prompt, please try again or contact support.");
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