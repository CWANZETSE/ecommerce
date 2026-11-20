<?php

namespace App\Notifications;

use App\Notifications\Channels\AfricasTalkingChannel;
use App\Services\AfricasTalking\AfricasTalkingMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return [
            AfricasTalkingChannel::class,
        ];
    }

    public function toAfricasTalking(object $notifiable): AfricasTalkingMessage
    {
        $message = "Hi {$notifiable->name}, welcome to My Store! "
            . "Thank you for creating an account with us. "
            . "You can now track orders, save delivery addresses, and checkout faster. "
            . "Start shopping at " . url('/customer/products');

        return AfricasTalkingMessage::create($message);
    }
}