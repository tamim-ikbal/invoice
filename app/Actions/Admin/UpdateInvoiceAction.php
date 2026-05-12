<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\UpdateInvoiceData;
use App\Models\Invoice;

class UpdateInvoiceAction
{
    public function handle(Invoice $invoice, UpdateInvoiceData $data): Invoice
    {
        $invoice->update($data->toArray());

        return $invoice;
    }
}
