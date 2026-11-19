<?php
namespace App\Events;
use App\Models\Order;
use App\Enums\OrderFulfilmentStatus;
use Illuminate\Foundation\Events\Dispatchable;
class OrderStatusChangedEvent {
    use Dispatchable;
    public function __construct(public Order $order, public OrderFulfilmentStatus $newStatus) {}
}