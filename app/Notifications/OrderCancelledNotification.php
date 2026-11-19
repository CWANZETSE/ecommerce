<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public bool $wasPaid
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Order Cancelled — {$this->order->order_number}")
            ->greeting("Hi {$this->order->customer_name},")
            ->line("Your order {$this->order->order_number} has been cancelled.");

        if ($this->wasPaid) {
            $mail->line("A refund of KES {$this->order->grand_total} will be processed back to your M-Pesa number within 3-5 business days.");
        }

        return $mail->line("If you have any questions, please contact our support team.");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_ulid' => $this->order->ulid,
            'order_number' => $this->order->order_number,
            'sms_message' => "Your order {$this->order->order_number} has been cancelled. " . ($this->wasPaid ? "Refund is processing." : ""),
        ];
    }
}