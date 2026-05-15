<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\PaymentData;
use App\Models\Payment;
use App\Notifications\PaymentRecordedNotification;

class UpdatePaymentAction
{
    public function handle(Payment $payment, PaymentData $data): Payment
    {
        $payment->update($data->toArray());

        $payment->load('invoice.client');

        if ($payment->invoice->client && $payment->status->shouldNotifyClient()) {
            $payment->invoice->client->notify(new PaymentRecordedNotification($payment));
        }

        return $payment;
    }
}
