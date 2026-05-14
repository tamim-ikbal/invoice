<?php

namespace App\Services;

use App\Http\Resources\PublicInvoiceResource;
use App\Models\Invoice;

class PublicInvoiceService
{
    /**
     * @return array{invoice: array<string, mixed>, invoiceId: int}
     */
    public function show(string $uid): array
    {
        $invoice = Invoice::where('uid', $uid)
            ->with(['items', 'payments', 'client'])
            ->firstOrFail();

        return [
            'invoice' => PublicInvoiceResource::make($invoice)->resolve(),
            'invoiceId' => $invoice->id,
        ];
    }
}
