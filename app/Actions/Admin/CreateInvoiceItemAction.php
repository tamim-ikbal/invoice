<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\InvoiceItemData;
use App\Models\Invoice;
use App\Models\InvoiceItem;

class CreateInvoiceItemAction
{
    public function handle(Invoice $invoice, InvoiceItemData $data): InvoiceItem
    {
        return $invoice->items()->create($data->toArray());
    }
}
