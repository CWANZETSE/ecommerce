<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderPaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;

        $mail = (new MailMessage)
            ->subject("Payment received — Order {$order->order_number}")
            ->greeting("Hi {$order->customer_name},")
            ->line("We've received your payment of KES {$order->grand_total} for order {$order->order_number}.")
            ->line('Your order is now being prepared for delivery.');

        foreach ($order->items as $item) {
            $mail->line("• {$item->product_name} — {$item->sku} × {$item->quantity} = KES {$item->line_total}");
        }

        $mail->line('Delivery to: ' . $order->shipping_address['county'] . ' — ' . $order->shipping_address['town']);
        $mail->line('Thank you for shopping with us.');

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_ulid' => $this->order->ulid,
            'order_number' => $this->order->order_number,
        ];
    }
}