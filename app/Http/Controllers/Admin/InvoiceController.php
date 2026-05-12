<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\CreateInvoiceAction;
use App\Actions\Admin\DeleteInvoiceAction;
use App\Actions\Admin\UpdateInvoiceAction;
use App\Actions\Admin\UpdateInvoiceSettingsAction;
use App\DTOs\Admin\CreateInvoiceData;
use App\DTOs\Admin\UpdateInvoiceData;
use App\DTOs\Admin\UpdateInvoiceSettingsData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateInvoiceRequest;
use App\Http\Requests\Admin\UpdateInvoiceRequest;
use App\Http\Requests\Admin\UpdateInvoiceSettingsRequest;
use App\Models\Invoice;
use App\Services\InvoiceEditService;
use App\Services\InvoiceIndexService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index(InvoiceIndexService $service): Response
    {
        return Inertia::render('admin/Invoice/Index', $service->handle());
    }

    /**
     * Store a newly created invoice.
     */
    public function store(CreateInvoiceRequest $request, CreateInvoiceAction $action): RedirectResponse
    {
        $invoice = $action->handle(CreateInvoiceData::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Invoice created.')]);

        return to_route('admin.invoices.edit', $invoice);
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(Invoice $invoice, InvoiceEditService $service): Response
    {
        return Inertia::render('admin/Invoice/Edit', $service->handle($invoice));
    }

    /**
     * Update the specified invoice.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice, UpdateInvoiceAction $action): RedirectResponse
    {
        $action->handle($invoice, UpdateInvoiceData::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Invoice updated.')]);

        return to_route('admin.invoices.edit', $invoice);
    }

    /**
     * Update the invoice settings.
     */
    public function updateSettings(UpdateInvoiceSettingsRequest $request, Invoice $invoice, UpdateInvoiceSettingsAction $action): RedirectResponse
    {
        $action->handle($invoice, UpdateInvoiceSettingsData::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Invoice settings updated.')]);

        return to_route('admin.invoices.edit', $invoice);
    }

    /**
     * Remove the specified invoice.
     */
    public function destroy(Invoice $invoice, DeleteInvoiceAction $action): RedirectResponse
    {
        $action->handle($invoice);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Invoice deleted.')]);

        return to_route('admin.invoices.index');
    }
}
