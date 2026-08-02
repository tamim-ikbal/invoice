<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\AdjustmentData;
use App\Models\Adjustment;
use App\Models\Invoice;

class CreateAdjustmentAction
{
    public function handle(Invoice $invoice, AdjustmentData $data): Adjustment
    {
        return $invoice->adjustments()->create($data->toArray());
    }
}
