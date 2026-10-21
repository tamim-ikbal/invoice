<?php

namespace App\Enums;

use Carbon\CarbonImmutable;

enum DashboardPeriodEnum: string
{
    case THREE_MONTHS = '3m';
    case SIX_MONTHS = '6m';
    case ONE_YEAR = '1y';
    case ALL_TIME = 'all';

    public function label(): string
    {
        return match ($this) {
            self::THREE_MONTHS => '3 Months',
            self::SIX_MONTHS => '6 Months',
            self::ONE_YEAR => '1 Year',
            self::ALL_TIME => 'All Time',
        };
    }

    /**
     * The first day included in the period, or null when the period is unbounded.
     */
    public function startDate(): ?CarbonImmutable
    {
        return match ($this) {
            self::THREE_MONTHS => CarbonImmutable::today()->subMonthsNoOverflow(3),
            self::SIX_MONTHS => CarbonImmutable::today()->subMonthsNoOverflow(6),
            self::ONE_YEAR => CarbonImmutable::today()->subYearNoOverflow(),
            self::ALL_TIME => null,
        };
    }
}
