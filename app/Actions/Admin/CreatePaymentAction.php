<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\PaymentData;
use App\Models\Invoice;
use App\Models\Payment;
use App\Notifications\PaymentRecordedNotification;

class CreatePaymentAction
{
    public function handle(Invoice $invoice, PaymentData $data): Payment
    {
        $payment = $invoice->payments()->create($data->toArray());

        if ($invoice->client && $payment->status->shouldNotifyClient()) {
            $payment->load('invoice.client');
            $invoice->client->notify(new PaymentRecordedNotification($payment));
        }

        return $payment;
    }
}
