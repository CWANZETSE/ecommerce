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

class OrderPaidNotification extends Notification implements ShouldQueue
{
    use HasConfigurableChannels;
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Payment Received - Order {$this->order->order_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line('Payment received!')
            ->line(
                "We have successfully received KES {$this->order->grand_total} for order {$this->order->order_number}."
            )
            ->line(
                'Your order is now being processed for delivery.'
            )
            ->line('Thank you for shopping with us!')
            ->salutation('Thank you.');
    }

    public function toAfricasTalking(
        object $notifiable
    ): AfricasTalkingMessage {
        $message = "Payment received! We have successfully received KES {$this->order->grand_total} "
            ."for order {$this->order->order_number}. Your order is now being processed for delivery.";

        return AfricasTalkingMessage::create($message);
    }

    public function toWhatsApp(object $notifiable): WhatsAppMessage
    {
        return WhatsAppMessage::text(
            'Hello, we have received your payment of KES '
            .number_format((float) $this->order->grand_total, 2)
            ." for order {$this->order->order_number}.\n\n"
            .'Your order is now being processed for delivery. Thank you for shopping with us!'
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_ulid' => $this->order->ulid,
            'order_number' => $this->order->order_number,
            'message' => "Payment received! Your order {$this->order->order_number} is now being processed.",
        ];
    }
}
