<?php

namespace App\Actions\Admin;

use App\Models\Invoice;
use App\Models\Payment;

class CreatePaymentAction
{
    /**
     * @param  array{amount: float, date: string, status: string, payment_method: string}  $data
     */
    public function handle(Invoice $invoice, array $data): Payment
    {
        return $invoice->payments()->create($data);
    }
}
