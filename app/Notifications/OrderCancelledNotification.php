<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Concerns\HasConfigurableChannels;
use App\Services\AfricasTalking\AfricasTalkingMessage;
use App\Services\WhatsApp\WhatsAppMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCancelledNotification extends Notification implements ShouldQueue
{
    use HasConfigurableChannels;
    use Queueable;

    public function __construct(
        public Order $order,
        public bool $wasPaid
    ) {}

    public function toMail(object $notifiable): MailMessage
{
    $body = [
        "Hello {$notifiable->name},",
        "Your order {$this->order->order_number} has been cancelled.",
    ];

    if ($this->wasPaid) {
        $body[] = "A refund of KES {$this->order->grand_total} is being processed.";
        $body[] = 'The amount will be returned to your M-Pesa number within 3-5 business days.';
    }

    $body[] = 'Contact our support team if you have any questions.';

    return (new MailMessage)
        ->subject("Order {$this->order->order_number} Cancelled")
        ->view('emails.notifications.layout', [
            'heading' => 'Order Cancelled',
            'body' => $body,
        ]);
}

    public function toAfricasTalking(
        object $notifiable
    ): AfricasTalkingMessage {
        $message = "Your order {$this->order->order_number} has been cancelled.";

        if ($this->wasPaid) {
            $message .= " Refund of KES {$this->order->grand_total} is processing and will be returned to your M-Pesa within 3-5 business days.";
        }

        $message .= ' Support.';

        return AfricasTalkingMessage::create($message);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_ulid' => $this->order->ulid,
            'order_number' => $this->order->order_number,
            'message' => "Your order {$this->order->order_number} has been cancelled."
                .($this->wasPaid ? ' Refund is processing.' : ''),
        ];
    }

    public function toWhatsApp(object $notifiable): WhatsAppMessage
    {
        if ($this->wasPaid) {
            return WhatsAppMessage::template('order_canceled', 'en_US')
                ->bodyParameters([
                    $notifiable->name,
                    $this->order->order_number,
                ]);
        }

        return WhatsAppMessage::text(
            "Hello {$notifiable->name}, your order {$this->order->order_number} has been cancelled."
        );
    }
}
