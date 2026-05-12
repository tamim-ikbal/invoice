<?php

namespace App\Actions\Admin;

use App\Models\Invoice;

class UpdateInvoiceAction
{
    /**
     * @param  array{title: string, client_id: int|null, status: string, date: string}  $data
     */
    public function handle(Invoice $invoice, array $data): Invoice
    {
        $invoice->update($data);

        return $invoice;
    }
}
