<?php

namespace App\Actions\Admin;

use App\Models\Payment;

class UpdatePaymentAction
{
    /**
     * @param  array{amount: float, date: string, status: string, payment_method: string}  $data
     */
    public function handle(Payment $payment, array $data): Payment
    {
        $payment->update($data);

        return $payment;
    }
}
