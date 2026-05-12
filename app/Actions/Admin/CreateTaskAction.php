<?php

namespace App\Actions\Admin;

use App\Models\Invoice;
use App\Models\Task;

class CreateTaskAction
{
    /**
     * @param  array{name: string, amount: float}  $data
     */
    public function handle(Invoice $invoice, array $data): Task
    {
        return $invoice->tasks()->create($data);
    }
}
