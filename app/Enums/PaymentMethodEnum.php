<?php

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case PAYONEER = 'payoneer';

    public function label(): string
    {
        return match ($this) {
            self::PAYONEER => 'Payoneer',
        };
    }
}
