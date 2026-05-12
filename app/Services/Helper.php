<?php

namespace App\Services;

use Carbon\CarbonInterface;

class Helper
{
    /**
     * Format a number as money with currency symbol.
     * e.g. "$1,234.00"
     */
    public static function moneyFormat(float|int|string $amount): string
    {
        return '$'.number_format((float) $amount, 2);
    }

    /**
     * Format a number to always have 2 decimal places.
     * e.g. "1234.00"
     */
    public static function numberFormat(float|int|string $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    /**
     * Round an amount to 2 decimal places for storage.
     */
    public static function roundAmount(float|int|string $amount): float
    {
        return round((float) $amount, 2);
    }

    /**
     * Format a date for display.
     * e.g. "12 May, 2026" or "12 May, 2026 02:30 pm" if time is present.
     */
    public static function dateFormat(?CarbonInterface $date, bool $withTime = false): ?string
    {
        if ($date === null) {
            return null;
        }

        if ($withTime) {
            return $date->format('d F, Y h:i a');
        }

        return $date->format('d F, Y');
    }
}
