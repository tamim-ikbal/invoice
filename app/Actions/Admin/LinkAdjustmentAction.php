<?php

namespace App\Actions\Admin;

use App\Models\Adjustment;
use App\Models\Invoice;

class LinkAdjustmentAction
{
    public function handle(Adjustment $adjustment, ?Invoice $invoice): Adjustment
    {
        $adjustment->update(['target_invoice_id' => $invoice?->id]);

        return $adjustment;
    }
}
