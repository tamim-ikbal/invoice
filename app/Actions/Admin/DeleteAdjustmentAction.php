<?php

namespace App\Actions\Admin;

use App\Models\Adjustment;

class DeleteAdjustmentAction
{
    public function handle(Adjustment $adjustment): void
    {
        $adjustment->delete();
    }
}
