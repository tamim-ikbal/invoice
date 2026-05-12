<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\InvoiceItemData;
use App\Models\InvoiceItem;

class UpdateInvoiceItemAction
{
    public function handle(InvoiceItem $item, InvoiceItemData $data): InvoiceItem
    {
        $item->update($data->toArray());

        return $item;
    }
}
