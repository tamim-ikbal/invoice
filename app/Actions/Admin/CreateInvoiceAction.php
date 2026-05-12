<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\CreateInvoiceData;
use App\Enums\InvoiceStatusEnum;
use App\Models\Invoice;

class CreateInvoiceAction
{
    public function handle(CreateInvoiceData $data): Invoice
    {
        return Invoice::create([
            'title' => $data->title,
            'status' => InvoiceStatusEnum::DRAFT,
        ]);
    }
}
