<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\AdjustmentData;
use App\Models\Adjustment;

class UpdateAdjustmentAction
{
    public function handle(Adjustment $adjustment, AdjustmentData $data): Adjustment
    {
        $adjustment->update($data->toArray());

        return $adjustment;
    }
}
