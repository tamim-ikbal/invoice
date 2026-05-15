<?php

namespace App\Services;

use App\Enums\InvoiceStatusEnum;
use App\Http\Resources\ClientResource;
use App\Http\Resources\InvoiceEditResource;
use App\Http\Resources\InvoiceResource;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection as ResourceCollection;
use Inertia\DeferProp;
use Inertia\Inertia;

class InvoiceService
{
    /**
     * @return array{invoices: ResourceCollection}
     */
    public function index(): array
    {
        return [
            'invoices' => InvoiceResource::collection(
                Invoice::with('client')->latest()->paginate()
            ),
        ];
    }

    /**
     * @return array{invoice: InvoiceEditResource, clients: AnonymousResourceCollection, statuses: array<int, array{value: string, label: string}>, items: DeferProp, payments: DeferProp}
     */
    public function edit(Invoice $invoice): array
    {
        $invoice->load(['items', 'payments', 'client']);

        return [
            'invoice' => InvoiceEditResource::make($invoice),
            'clients' => ClientResource::collection(Client::all()),
            'statuses' => array_map(
                fn ($case) => ['value' => $case->value, 'label' => $case->label()],
                InvoiceStatusEnum::cases()
            ),
            'items' => Inertia::defer(fn () => $invoice->items->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'quantity' => $item->quantity,
                'amount' => Helper::numberFormat($item->amount),
            ])),
            'payments' => Inertia::defer(fn () => $invoice->payments->map(fn ($payment) => [
                'id' => $payment->id,
                'title' => $payment->title,
                'amount' => Helper::numberFormat($payment->amount),
                'date' => $payment->date->format('Y-m-d'),
                'status' => $payment->status,
                'payment_method' => $payment->payment_method,
            ])),
        ];
    }
}
