<?php

namespace App\Services;

use App\Enums\InvoiceStatusEnum;
use App\Http\Resources\ClientResource;
use App\Http\Resources\InvoiceEditResource;
use App\Http\Resources\InvoiceResource;
use App\Models\Adjustment;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection as ResourceCollection;
use Illuminate\Support\Collection;
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
                Invoice::with(['client', 'payments', 'items', 'adjustmentsIn', 'adjustments'])
                    ->latest()
                    ->paginate()
            ),
        ];
    }

    /**
     * @return array{invoice: InvoiceEditResource, clients: AnonymousResourceCollection, statuses: array<int, array{value: string, label: string}>, items: DeferProp, payments: DeferProp, adjustments: DeferProp, pendingAdjustments: DeferProp, targetInvoices: DeferProp}
     */
    public function edit(Invoice $invoice): array
    {
        $invoice->load([
            'items',
            'payments',
            'client',
            'adjustments.targetInvoice',
            'adjustmentsIn.sourceInvoice',
        ]);

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
                'bdt_rate' => $payment->bdt_rate ? Helper::numberFormat($payment->bdt_rate) : null,
            ])),
            'adjustments' => Inertia::defer(fn () => $invoice->adjustments
                ->map(fn ($adjustment) => [
                    'id' => $adjustment->id,
                    'direction' => 'out',
                    'amount' => Helper::numberFormat($adjustment->amount),
                    'note' => $adjustment->note,
                    'invoice' => $this->invoiceStub($adjustment->targetInvoice),
                ])
                ->concat($invoice->adjustmentsIn->map(fn ($adjustment) => [
                    'id' => $adjustment->id,
                    'direction' => 'in',
                    'amount' => Helper::numberFormat($adjustment->amount),
                    'note' => $adjustment->note,
                    'invoice' => $this->invoiceStub($adjustment->sourceInvoice),
                ]))
                ->values()),
            'pendingAdjustments' => Inertia::defer(fn () => $this->pendingAdjustments($invoice)),
            'targetInvoices' => Inertia::defer(fn () => $this->targetInvoices($invoice)),
        ];
    }

    /**
     * Get the unapplied balances from other invoices that this invoice may take on.
     *
     * @return Collection<int, array<string, mixed>>
     */
    protected function pendingAdjustments(Invoice $invoice): Collection
    {
        return Adjustment::query()
            ->whereNull('target_invoice_id')
            ->where('source_invoice_id', '!=', $invoice->id)
            ->whereHas('sourceInvoice', fn ($query) => $query->where('client_id', $invoice->client_id))
            ->with('sourceInvoice')
            ->get()
            ->map(fn ($adjustment) => [
                'id' => $adjustment->id,
                'amount' => Helper::numberFormat($adjustment->amount),
                'note' => $adjustment->note,
                'invoice' => $this->invoiceStub($adjustment->sourceInvoice),
            ]);
    }

    /**
     * Get the invoices this invoice may carry a balance to.
     *
     * @return Collection<int, array<string, mixed>>
     */
    protected function targetInvoices(Invoice $invoice): Collection
    {
        return Invoice::query()
            ->whereKeyNot($invoice->id)
            ->where('client_id', $invoice->client_id)
            ->latest()
            ->get()
            ->map(fn ($candidate) => [
                'id' => $candidate->id,
                'invoice_no' => $candidate->invoice_no,
                'title' => $candidate->title,
            ]);
    }

    /**
     * @return array{id: int, invoice_no: string|null}|null
     */
    protected function invoiceStub(?Invoice $invoice): ?array
    {
        if (! $invoice) {
            return null;
        }

        return ['id' => $invoice->id, 'invoice_no' => $invoice->invoice_no];
    }
}
