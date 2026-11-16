<?php

namespace App\Notifications;

use App\Enums\OrderPaymentStatus;
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
        public bool $refundInitiated = false,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Order cancelled — {$this->order->order_number}")
            ->greeting("Hi {$this->order->customer_name},")
            ->line("Your order {$this->order->order_number} has been cancelled.");

        if ($this->refundInitiated) {
            $mail->line("A refund of KES {$this->order->grand_total} will be processed back to your M-Pesa number within 3–5 business days.");
        } elseif ($this->order->payment_status === OrderPaymentStatus::Paid) {
            $mail->line('Your payment will be refunded separately. Contact us if you don’t receive the refund within 5 business days.');
        }

        $mail->line('If you have questions, reply to this email or contact our support team.');

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_ulid' => $this->order->ulid,
            'refunded' => $this->refunded,
        ];
    }
}
