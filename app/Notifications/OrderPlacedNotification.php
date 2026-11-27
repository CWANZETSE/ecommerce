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

class OrderPlacedNotification extends Notification implements ShouldQueue
{
    use HasConfigurableChannels;
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->subject("Order Received - {$this->order->order_number}")
        ->view('emails.notifications.layout', [
            'heading' => 'Your Order Has Been Received',

            'body' => [
                "Hello {$notifiable->name},",

                "Thank you for shopping with Bazario. Your order {$this->order->order_number} has been successfully received.",

                "Your order total is KES {$this->order->grand_total}. Please follow the M-Pesa payment prompt to complete your payment.",

                'We appreciate your business.',
            ],
        ]);
}

    public function toAfricasTalking(
        object $notifiable
    ): AfricasTalkingMessage {
        $message = "Thank you for your order! Order {$this->order->order_number} "
            ."has been received for KES {$this->order->grand_total}. "
            .'Please check your phone for the M-Pesa payment prompt and enter your PIN to complete payment. '
            .'If you do not receive the prompt, please try again or contact support.';

        return AfricasTalkingMessage::create($message);
    }

    public function toWhatsApp(object $notifiable): WhatsAppMessage
    {
        return WhatsAppMessage::template('order_processing2', 'en_US')
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
            'message' => "Order {$this->order->order_number} received. Please check your phone for the M-Pesa prompt to complete payment.",
        ];
    }
}
