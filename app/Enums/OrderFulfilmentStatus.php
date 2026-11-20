<?php

namespace App\Enums;

enum OrderFulfilmentStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Dispatched = 'dispatched';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    /**
     * Determine if the order can transition to the given status.
     */
    public function canTransitionTo(OrderFulfilmentStatus $newStatus): bool
    {
        // 1. No going back if already in a final state
        if ($this === self::Delivered || $this === self::Cancelled) {
            return false;
        }

        // 2. Cancelled can be achieved at any stage EXCEPT Delivered
        if ($newStatus === self::Cancelled) {
            return true;
        }

        // 3. Strict step-by-step forward progression
        return match ($this) {
            self::Pending => $newStatus === self::Processing,
            self::Processing => $newStatus === self::Dispatched,
            self::Dispatched => $newStatus === self::Delivered,
            default => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Processing => 'Processing',
            self::Dispatched => 'Dispatched',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }

    public function canGenerateReceipt(): bool
{
    return in_array($this, [self::Dispatched, self::Delivered], true);
}
}