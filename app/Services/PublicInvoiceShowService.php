<?php

namespace App\Services;

use App\Http\Resources\PublicInvoiceResource;
use App\Models\Invoice;

class PublicInvoiceShowService
{
    /**
     * @return array{invoice: PublicInvoiceResource}
     */
    public function handle(string $uid): array
    {
        $invoice = Invoice::where('uid', $uid)
            ->with(['tasks', 'payments', 'client'])
            ->firstOrFail();

        return [
            'invoice' => PublicInvoiceResource::make($invoice)->resolve(),
        ];
    }
}
