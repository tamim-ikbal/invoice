<?php

namespace App\Services;

use App\Enums\DashboardPeriodEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * @return array{period: string, periods: array<int, array{value: string, label: string}>, stats: array{total_usd: string, total_bdt: string, total_invoices: int, total_clients: int}}
     */
    public function index(DashboardPeriodEnum $period): array
    {
        $startDate = $period->startDate();

        $paidPayments = Payment::query()
            ->where('status', PaymentStatusEnum::PAID)
            ->whereHas('invoice')
            ->when($startDate, fn (Builder $query) => $query->whereDate('date', '>=', $startDate));

        return [
            'period' => $period->value,
            'periods' => array_map(
                fn ($case) => ['value' => $case->value, 'label' => $case->label()],
                DashboardPeriodEnum::cases()
            ),
            'stats' => [
                'total_usd' => Helper::moneyFormat((clone $paidPayments)->sum('amount')),
                'total_bdt' => Helper::bdtMoneyFormat((clone $paidPayments)->sum(DB::raw('amount * COALESCE(bdt_rate, 0)'))),
                'total_invoices' => Invoice::query()
                    ->when($startDate, fn (Builder $query) => $query->whereDate('date', '>=', $startDate))
                    ->count(),
                'total_clients' => Client::query()
                    ->when($startDate, fn (Builder $query) => $query->where('created_at', '>=', $startDate))
                    ->count(),
            ],
        ];
    }
}
