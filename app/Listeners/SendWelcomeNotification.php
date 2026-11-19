<?php

namespace App\Listeners;
use App\Events\UserRegisteredEvent;
use App\Notifications\WelcomeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWelcomeNotification implements ShouldQueue {
    public function handle(UserRegisteredEvent $event): void {
        $event->user->notify(new WelcomeNotification());
    }
}