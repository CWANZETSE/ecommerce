<?php

namespace App\Enums;

enum OrderFulfilmentStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Dispatched = 'dispatched';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
}