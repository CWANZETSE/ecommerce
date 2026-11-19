<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Payment Received — {$this->order->order_number}")
            ->greeting("Hi {$this->order->customer_name},")
            ->line("We have successfully received your payment of KES {$this->order->grand_total}.")
            ->line("Your order is now being processed for delivery.");

        foreach ($this->order->items as $item) {
            $mail->line("• {$item->product_name} × {$item->quantity}");
        }

        return $mail->action('View Receipt', url("/checkout/success/{$this->order->ulid}"));
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