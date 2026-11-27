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

class AdminOrderPlacedNotification extends Notification implements ShouldQueue
{
    use HasConfigurableChannels;
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    /**
     * Admin email notification.
     */
    public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->subject("New Order Received - {$this->order->order_number}")
        ->view('emails.notifications.layout', [
            'heading' => 'New Order Received',
            'body' => [
                "Hello {$notifiable->name},",
                'A new order has been placed on Bazario.',
                "Order Number: {$this->order->order_number}",
                "Customer: {$this->order->customer_name}",
                "Customer Email: {$this->order->customer_email}",
                "Customer Phone: {$this->order->customer_phone}",
                "Order Total: KES {$this->order->grand_total}",
                'Please log in to the administration dashboard to review and process this order.',
            ],
        ]);
}

    /**
     * Admin SMS notification.
     */
    public function toAfricasTalking(
        object $notifiable
    ): AfricasTalkingMessage {
        $message = "BAZARIO: New order received. "
            ."Order: {$this->order->order_number}. "
            ."Customer: {$this->order->customer_name}. "
            ."Total: KES {$this->order->grand_total}. "
            ."Please check admin dashboard.";

        return AfricasTalkingMessage::create($message);
    }

    /**
     * Admin WhatsApp notification.
     */
    public function toWhatsApp(
        object $notifiable
    ): WhatsAppMessage {
        return WhatsAppMessage::template(
            'admin_order_placed',
            'en_US'
        )->bodyParameters([
            $this->order->order_number,
            $this->order->customer_name,
            $this->order->customer_phone,
            $this->order->grand_total,
        ]);
    }

    /**
     * Admin database notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'admin_order_placed',
            'order_ulid' => $this->order->ulid,
            'order_number' => $this->order->order_number,
            'customer_name' => $this->order->customer_name,
            'customer_email' => $this->order->customer_email,
            'customer_phone' => $this->order->customer_phone,
            'grand_total' => $this->order->grand_total,
            'message' => "New order {$this->order->order_number} received from {$this->order->customer_name}.",
        ];
    }
}
