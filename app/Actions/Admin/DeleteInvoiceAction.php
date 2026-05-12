<?php

namespace App\Actions\Admin;

use App\Models\Invoice;

class DeleteInvoiceAction
{
    public function handle(Invoice $invoice): void
    {
        $invoice->delete();
    }
}
