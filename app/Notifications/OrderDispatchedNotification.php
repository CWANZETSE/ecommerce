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

class OrderDispatchedNotification extends Notification implements ShouldQueue
{
    use HasConfigurableChannels;
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    public function toMail(object $notifiable): MailMessage
{
    $body = [
        "Hello {$notifiable->name},",
        "Good news! Your order {$this->order->order_number} has been dispatched and is on its way to you.",
    ];

    $shippingAddress = $this->order->shipping_address;

    if (
        ! empty($shippingAddress['county'])
        && ! empty($shippingAddress['address'])
    ) {
        $body[] = "Delivery to {$shippingAddress['county']}, {$shippingAddress['address']}.";
    }

    $body[] = 'Thank you for shopping with us!';

    return (new MailMessage)
        ->subject("Order {$this->order->order_number} Dispatched")
        ->view('emails.notifications.layout', [
            'heading' => 'Your Order Is On Its Way',
            'body' => $body,
        ]);
}

    public function toAfricasTalking(
        object $notifiable
    ): AfricasTalkingMessage {
        $message = "Good news! Your order {$this->order->order_number} has been dispatched and is on its way to you.";

        $shippingAddress = $this->order->shipping_address;

        if (
            ! empty($shippingAddress['county'])
            && ! empty($shippingAddress['address'])
        ) {
            $message .= " Delivery to {$shippingAddress['county']}, {$shippingAddress['address']}.";
        }

        return AfricasTalkingMessage::create($message);
    }

    public function toWhatsApp(object $notifiable): WhatsAppMessage
    {
        return WhatsAppMessage::template('order_dispatched', 'en_US')
            ->bodyParameters([
                $notifiable->name,
                $this->order->order_number,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_ulid' => $this->order->ulid,
            'order_number' => $this->order->order_number,
            'message' => "Good news! Your order {$this->order->order_number} has been dispatched and is on its way.",
        ];
    }
}
