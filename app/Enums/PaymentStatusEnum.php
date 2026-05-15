<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case UNPAID = 'unpaid';
    case PENDING = 'pending';
    case PAID = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::UNPAID => 'Unpaid',
            self::PENDING => 'Pending',
            self::PAID => 'Paid',
        };
    }

    public function shouldNotifyClient(): bool
    {
        return in_array($this, [self::PENDING, self::PAID]);
    }
}
