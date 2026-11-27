<?php

namespace App\Notifications;

use App\Notifications\Concerns\HasConfigurableChannels;
use App\Services\AfricasTalking\AfricasTalkingMessage;
use App\Services\WhatsApp\WhatsAppMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use HasConfigurableChannels;
    use Queueable;

    public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->subject('Welcome to Bazario!')
        ->view('emails.notifications.layout', [
            'heading' => 'Welcome to Bazario!',
            'body' => [
                "Hello {$notifiable->name},",
                'Thank you for creating an account with us.',
                'You can now track orders, save delivery addresses, and checkout faster.',
                [
                    'type' => 'action',
                    'label' => 'Start Shopping',
                    'url' => url('/customer/products'),
                ],
                'We look forward to serving you!',
            ],
        ]);
}

    public function toAfricasTalking(
        object $notifiable
    ): AfricasTalkingMessage {
        $message = "Hi {$notifiable->name}, welcome to My Store! "
            .'Thank you for creating an account with us. '
            .'You can now track orders, save delivery addresses, and checkout faster. '
            .'Start shopping at '.url('/customer/products');

        return AfricasTalkingMessage::create($message);
    }

    public function toWhatsApp(object $notifiable): WhatsAppMessage
    {
        return WhatsAppMessage::text(
            "Welcome to our store!\n\n"
            ."Your account has been created successfully. We're glad to have you with us.\n\n"
            .'You can now explore our products and start shopping. Thank you for choosing us!'
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Welcome to the Technology Hub, {$notifiable->name}! Thank you for creating an account with us.",
            'url' => url('/customer/products'),
        ];
    }
}
