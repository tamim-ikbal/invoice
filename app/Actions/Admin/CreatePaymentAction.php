<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\PaymentData;
use App\Models\Invoice;
use App\Models\Payment;

class CreatePaymentAction
{
    public function handle(Invoice $invoice, PaymentData $data): Payment
    {
        return $invoice->payments()->create($data->toArray());
    }
}
