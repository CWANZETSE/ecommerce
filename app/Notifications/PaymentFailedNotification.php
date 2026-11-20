<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Channels\AfricasTalkingChannel;
use App\Services\AfricasTalking\AfricasTalkingMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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
        return [
            AfricasTalkingChannel::class,
        ];
    }

    public function toAfricasTalking(object $notifiable): AfricasTalkingMessage
    {
        $message = "Payment failed for order {$this->order->order_number}. "
            . "Reason: {$this->reason}. "
            . "Your order is still open. Please try paying again or contact support if the issue persists. "
            . "Order total: KES {$this->order->grand_total}. "
            . "If money was deducted, it will be reversed within 48 hours.";

        return AfricasTalkingMessage::create($message);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_ulid' => $this->order->ulid,
            'reason' => $this->reason,
        ];
    }
}