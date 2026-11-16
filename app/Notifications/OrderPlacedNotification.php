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
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;

        $mail = (new MailMessage)
            ->subject("Order received — {$order->order_number}")
            ->greeting("Hi {$order->customer_name},")
            ->line("Thanks for your order! We've received order {$order->order_number} for KES {$order->grand_total}.")
            ->line('We\'ve sent an M-Pesa payment prompt to your phone. Enter your PIN to complete payment.');

        foreach ($order->items as $item) {
            $mail->line("• {$item->product_name} — {$item->sku} × {$item->quantity} = KES {$item->line_total}");
        }

        $address = $order->shipping_address;
        $mail->line("Delivery to: {$address['county']} — {$address['town']}, {$address['address']}");

        if (! empty($address['instructions'])) {
            $mail->line("Instructions: {$address['instructions']}");
        }

        $mail->line('You will receive another email once payment is confirmed.');

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