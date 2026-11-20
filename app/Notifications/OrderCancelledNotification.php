<?php

namespace App\Notifications;

use App\Models\Order;
use App\Services\AfricasTalking\AfricasTalkingMessage;
use App\Notifications\Channels\AfricasTalkingChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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
        return [
            AfricasTalkingChannel::class,
            'database',
        ];
    }

    public function toAfricasTalking(object $notifiable): AfricasTalkingMessage
    {
        $message = "Your order {$this->order->order_number} has been cancelled.";

        if ($this->wasPaid) {
            $message .= " Refund of KES {$this->order->grand_total} is processing and will be returned to your M-Pesa number within 3-5 business days.";
        }

        $message .= " Contact our support team if you have questions.";

        return AfricasTalkingMessage::create($message);
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
