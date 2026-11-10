<?php

namespace App\Enums;

enum OrderStatus: string
{
    case New = 'new';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Cancelled = 'cancelled';
}
