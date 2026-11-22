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
            ->greeting("Hello {$notifiable->name},")
            ->line('Thank you for your order!')
            ->line(
                "Order {$this->order->order_number} has been received for KES {$this->order->grand_total}."
            )
            ->line(
                'Please check your phone for the M-Pesa payment prompt and enter your PIN to complete payment.'
            )
            ->line(
                'If you do not receive the prompt, please try again or contact our support team.'
            )
            ->salutation('Thank you for shopping with us!');
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
        return WhatsAppMessage::text(
            "Hello, thank you for placing order {$this->order->order_number}!\n\n"
            .'Your order total is KES '
            .number_format((float) $this->order->grand_total, 2)
            .".\n\n"
            .'If you selected M-Pesa, please check your phone for the payment prompt and complete the payment.'
        );
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
