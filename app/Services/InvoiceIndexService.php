<?php

namespace App\Services;

use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;

class InvoiceIndexService
{
    /**
     * @return array{invoices: \Illuminate\Http\Resources\Json\AnonymousResourceCollection}
     */
    public function handle(): array
    {
        $invoices = Invoice::with('client')->latest()->paginate();

        return [
            'invoices' => InvoiceResource::collection($invoices),
        ];
    }
}
