<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case PAID = 'paid';
    case UNPAID = 'unpaid';

    public function label(): string
    {
        return match ($this) {
            self::PAID => 'Paid',
            self::UNPAID => 'Unpaid',
        };
    }
}
