<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\PaymentData;
use App\Models\Payment;

class UpdatePaymentAction
{
    public function handle(Payment $payment, PaymentData $data): Payment
    {
        $payment->update($data->toArray());

        return $payment;
    }
}
