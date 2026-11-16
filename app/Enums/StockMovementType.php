<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Initial = 'initial';
    case Receive = 'receive';
    case Adjust = 'adjust';
    case Damage = 'damage';
    case Sale = 'sale';
    case Return = 'return';
    case RefundReturn = 'refund_return';

    public function label(): string
    {
        return match ($this) {
            self::Initial => 'Initial stock',
            self::Receive => 'Stock received',
            self::Adjust => 'Adjustment',
            self::Damage => 'Damaged / lost',
            self::Sale => 'Sale',
            self::Return => 'Customer return',
            self::RefundReturn => 'Refund return',
        };
    }

    /** Movements that increase stock (positive quantity_change). */
    public function isInbound(): bool
    {
        return in_array($this, [self::Receive, self::Return, self::RefundReturn, self::Initial], true);
    }

    /** Movements that decrease stock (negative quantity_change). */
    public function isOutbound(): bool
    {
        return in_array($this, [self::Sale, self::Damage], true);
    }
}