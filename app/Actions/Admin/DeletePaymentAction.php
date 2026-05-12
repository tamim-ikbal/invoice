<?php

namespace App\Actions\Admin;

use App\Models\Payment;

class DeletePaymentAction
{
    public function handle(Payment $payment): void
    {
        $payment->delete();
    }
}
