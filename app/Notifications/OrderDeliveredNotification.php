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

class OrderDeliveredNotification extends Notification implements ShouldQueue
{
    use HasConfigurableChannels;
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Order {$this->order->order_number} Delivered")
            ->greeting("Hello {$notifiable->name},")
            ->line(
                "Your order {$this->order->order_number} has been successfully delivered."
            )
            ->line('Thank you for shopping with us!')
            ->salutation('Thank you.');
    }

    public function toAfricasTalking(
        object $notifiable
    ): AfricasTalkingMessage {
        $message = "Your order {$this->order->order_number} has been successfully delivered. "
            .'Thank you for shopping with us!';

        return AfricasTalkingMessage::create($message);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_ulid' => $this->order->ulid,
            'order_number' => $this->order->order_number,
            'message' => "Order {$this->order->order_number} has been delivered. Thank you for shopping with us!",
        ];
    }

    public function toWhatsApp(object $notifiable): WhatsAppMessage
    {
        return WhatsAppMessage::template('order_delivered', 'en_US')
            ->bodyParameters([
                $notifiable->name,
                $this->order->order_number,
            ]);
    }
}
