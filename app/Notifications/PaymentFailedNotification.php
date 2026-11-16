<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $reason,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Payment failed — Order {$this->order->order_number}")
            ->greeting("Hi {$this->order->customer_name},")
            ->line("Unfortunately, we couldn't process the payment for order {$this->order->order_number}.")
            ->line("Reason: {$this->reason}")
            ->line('Your order is still open. You can try paying again or contact us if the issue persists.')
            ->line("Order total: KES {$this->order->grand_total}")
            ->line('If money was deducted from your account, it will be reversed by M-Pesa within 48 hours.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_ulid' => $this->order->ulid,
            'reason' => $this->reason,
        ];
    }
}