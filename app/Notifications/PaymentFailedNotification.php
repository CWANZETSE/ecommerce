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

class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use HasConfigurableChannels;
    use Queueable;

    public function __construct(
        public Order $order,
        public string $reason,
    ) {}

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Payment Failed - Order {$this->order->order_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line(
                "Unfortunately, payment for order {$this->order->order_number} could not be completed."
            )
            ->line("Reason: {$this->reason}.")
            ->line(
                'Your order is still open. Please try paying again or contact our support team if the issue persists.'
            )
            ->line("Order total: KES {$this->order->grand_total}.")
            ->line(
                'If money was deducted, it will be reversed within 48 hours.'
            )
            ->salutation('Thank you.');
    }

    public function toAfricasTalking(
        object $notifiable
    ): AfricasTalkingMessage {
        $message = "Payment failed for order {$this->order->order_number}. "
            ."Reason: {$this->reason}. "
            .'Your order is still open. Please try paying again or contact support if the issue persists. '
            ."Order total: KES {$this->order->grand_total}. "
            .'If money was deducted, it will be reversed within 48 hours.';

        return AfricasTalkingMessage::create($message);
    }

    public function toWhatsApp(object $notifiable): WhatsAppMessage
    {
        return WhatsAppMessage::text(
            "Hello, we were unable to process payment for order {$this->order->order_number}.\n\n"
            ."Reason: {$this->reason}\n\n"
            ."Your order is still open. Please try making the payment again or contact our support team for assistance.\n\n"
            .'Order total: KES '
            .number_format((float) $this->order->grand_total, 2)
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_ulid' => $this->order->ulid,
            'order_number' => $this->order->order_number,
            'reason' => $this->reason,
            'message' => "Payment failed for order {$this->order->order_number}. Please try paying again or contact support.",
        ];
    }
}
