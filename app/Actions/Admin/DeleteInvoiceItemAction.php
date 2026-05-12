<?php

namespace App\Actions\Admin;

use App\Models\InvoiceItem;

class DeleteInvoiceItemAction
{
    public function handle(InvoiceItem $item): void
    {
        $item->delete();
    }
}
