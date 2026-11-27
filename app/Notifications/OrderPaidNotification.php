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
        ->view('emails.notifications.layout', [
            'heading' => 'Payment Successfully Received',
            'body' => [
                "Hello {$notifiable->name},",
                'Payment received!',
                "We have successfully received KES {$this->order->grand_total} for order {$this->order->order_number}.",
                'Your order is now being processed for delivery.',
                'Thank you for shopping with us!',
            ],
        ]);
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
        return WhatsAppMessage::template('payment_confirmation', 'en_US')
            ->bodyParameters([
                $notifiable->name,
                number_format((float) $this->order->grand_total, 2, '.', ','),
                $this->order->order_number,
            ]);
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
