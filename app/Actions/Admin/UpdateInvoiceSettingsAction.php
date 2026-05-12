<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\UpdateInvoiceSettingsData;
use App\Models\Invoice;

class UpdateInvoiceSettingsAction
{
    public function handle(Invoice $invoice, UpdateInvoiceSettingsData $data): Invoice
    {
        $invoice->update([
            'settings' => array_merge($invoice->settings ?? [], $data->toArray()),
        ]);

        return $invoice;
    }
}
