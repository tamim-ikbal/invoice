<?php

namespace App\Actions\Admin;

use App\Models\Adjustment;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class DeleteInvoiceAction
{
    public function handle(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            Adjustment::where('source_invoice_id', $invoice->id)
                ->orWhere('target_invoice_id', $invoice->id)
                ->delete();

            $invoice->delete();
        });
    }
}
