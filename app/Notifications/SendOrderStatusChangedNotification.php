<?php

namespace App\Listeners;

use App\Events\OrderStatusChangedEvent;
use App\Enums\OrderFulfilmentStatus;
use App\Notifications\OrderDispatchedNotification;
use App\Notifications\OrderDeliveredNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class SendOrderStatusChangedNotification
{
    public function handle(OrderStatusChangedEvent $event): void
    {
        $order = $event->order;
        $status = $event->newStatus;

        Log::info('Listener: SendOrderStatusChangedNotification started', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'new_status' => $status->value,
        ]);

        // Determine if this status change requires a notification
        $notification = match ($status) {
            OrderFulfilmentStatus::Dispatched => new OrderDispatchedNotification($order),
            OrderFulfilmentStatus::Delivered => new OrderDeliveredNotification($order),
            default => null,
        };

        if (! $notification) {
            Log::debug('Listener: No notification required for this status change', [
                'order_number' => $order->order_number,
                'status' => $status->value,
            ]);
            return;
        }

        $notificationClass = get_class($notification);

        Log::info('Listener: Dispatching status notification', [
            'order_number' => $order->order_number,
            'notification' => $notificationClass,
            'recipient' => $order->customer_email,
        ]);

        try {
            Notification::route('mail', $order->customer_email)->notify($notification);

            Log::info('Listener: Notification queued/sent successfully', [
                'order_number' => $order->order_number,
                'notification' => $notificationClass,
            ]);
        } catch (Throwable $e) {
            // Catch the exception so the Admin's status update succeeds even if the email fails
            Log::error('Listener: Failed to dispatch notification', [
                'order_number' => $order->order_number,
                'notification' => $notificationClass,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}