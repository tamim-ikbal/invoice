<?php

namespace App\Actions\Admin;

use App\Enums\InvoiceStatusEnum;
use App\Models\Invoice;

class CreateInvoiceAction
{
    public function handle(array $data): Invoice
    {
        return Invoice::create([
            'title' => $data['title'],
            'status' => InvoiceStatusEnum::DRAFT,
            'date' => now()->toDateString(),
        ]);
    }
}
