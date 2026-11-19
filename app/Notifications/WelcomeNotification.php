<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Welcome to My Store!")
            ->greeting("Hi {$notifiable->name},")
            ->line("Thank you for creating an account with us.")
            ->line("You can now track orders, save delivery addresses, and checkout faster.")
            ->action('Start Shopping', url('/customer/products'));
    }
}