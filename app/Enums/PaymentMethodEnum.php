<?php

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case PAYONEER = 'payoneer';
    case PAYONEER_ACH = 'payoneer_ach';

    public function label(): string
    {
        return match ($this) {
            self::PAYONEER => 'Payoneer',
            self::PAYONEER_ACH => 'Payoneer ACH',
        };
    }
}
