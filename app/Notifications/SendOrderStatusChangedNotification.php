<?php

namespace App\Listeners;

use App\Enums\OrderFulfilmentStatus;
use App\Events\OrderStatusChangedEvent;
use App\Notifications\OrderDeliveredNotification;
use App\Notifications\OrderDispatchedNotification;
use Illuminate\Support\Facades\Log;
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

        $notification = match ($status) {
            OrderFulfilmentStatus::Dispatched =>
                new OrderDispatchedNotification($order),

            OrderFulfilmentStatus::Delivered =>
                new OrderDeliveredNotification($order),

            default => null,
        };

        if (!$notification) {
            Log::debug('Listener: No notification required for this status change', [
                'order_number' => $order->order_number,
                'status' => $status->value,
            ]);

            return;
        }

        try {
            $user = $order->user;

            if (!$user) {
                Log::warning('Listener: Order customer not found. Notification skipped.', [
                    'order_number' => $order->order_number,
                    'notification' => class_basename($notification),
                ]);

                return;
            }

            Log::info('Listener: Dispatching status notification', [
                'order_number' => $order->order_number,
                'notification' => class_basename($notification),
                'recipient_id' => $user->getKey(),
            ]);

            $user->notify($notification);

            Log::info('Listener: Status notification dispatched successfully', [
                'order_number' => $order->order_number,
                'notification' => class_basename($notification),
                'recipient_id' => $user->getKey(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Listener: Failed to dispatch status notification', [
                'order_number' => $order->order_number,
                'notification' => class_basename($notification),
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
