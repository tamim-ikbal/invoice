<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\CreateInvoiceItemAction;
use App\Actions\Admin\DeleteInvoiceItemAction;
use App\Actions\Admin\UpdateInvoiceItemAction;
use App\DTOs\Admin\InvoiceItemData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreInvoiceItemRequest;
use App\Http\Requests\Admin\UpdateInvoiceItemRequest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class InvoiceItemController extends Controller
{
    /**
     * Store a newly created item for the given invoice.
     */
    public function store(StoreInvoiceItemRequest $request, Invoice $invoice, CreateInvoiceItemAction $action): RedirectResponse
    {
        $action->handle($invoice, InvoiceItemData::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Item added.')]);

        return to_route('admin.invoices.edit', $invoice);
    }

    /**
     * Update the specified item.
     */
    public function update(UpdateInvoiceItemRequest $request, Invoice $invoice, InvoiceItem $invoiceItem, UpdateInvoiceItemAction $action): RedirectResponse
    {
        $action->handle($invoiceItem, InvoiceItemData::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Item updated.')]);

        return to_route('admin.invoices.edit', $invoice);
    }

    /**
     * Remove the specified item.
     */
    public function destroy(Invoice $invoice, InvoiceItem $invoiceItem, DeleteInvoiceItemAction $action): RedirectResponse
    {
        $action->handle($invoiceItem);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Item deleted.')]);

        return to_route('admin.invoices.edit', $invoice);
    }
}
