<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\CreateAdjustmentAction;
use App\Actions\Admin\DeleteAdjustmentAction;
use App\Actions\Admin\LinkAdjustmentAction;
use App\Actions\Admin\UpdateAdjustmentAction;
use App\DTOs\Admin\AdjustmentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LinkAdjustmentRequest;
use App\Http\Requests\Admin\StoreAdjustmentRequest;
use App\Http\Requests\Admin\UpdateAdjustmentRequest;
use App\Models\Adjustment;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class AdjustmentController extends Controller
{
    /**
     * Store a newly created adjustment carried out of the given invoice.
     */
    public function store(StoreAdjustmentRequest $request, Invoice $invoice, CreateAdjustmentAction $action): RedirectResponse
    {
        $action->handle($invoice, AdjustmentData::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Adjustment added.')]);

        return to_route('admin.invoices.edit', $invoice);
    }

    /**
     * Update the specified adjustment.
     */
    public function update(UpdateAdjustmentRequest $request, Invoice $invoice, Adjustment $adjustment, UpdateAdjustmentAction $action): RedirectResponse
    {
        $action->handle($adjustment, AdjustmentData::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Adjustment updated.')]);

        return to_route('admin.invoices.edit', $invoice);
    }

    /**
     * Remove the specified adjustment.
     */
    public function destroy(Invoice $invoice, Adjustment $adjustment, DeleteAdjustmentAction $action): RedirectResponse
    {
        $action->handle($adjustment);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Adjustment deleted.')]);

        return to_route('admin.invoices.edit', $invoice);
    }

    /**
     * Carry an unapplied adjustment into the given invoice.
     */
    public function link(LinkAdjustmentRequest $request, Invoice $invoice, Adjustment $adjustment, LinkAdjustmentAction $action): RedirectResponse
    {
        $action->handle($adjustment, $invoice);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Balance carried forward.')]);

        return to_route('admin.invoices.edit', $invoice);
    }

    /**
     * Detach an adjustment from the invoice it was carried into.
     */
    public function unlink(Invoice $invoice, Adjustment $adjustment, LinkAdjustmentAction $action): RedirectResponse
    {
        $action->handle($adjustment, null);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Carried balance removed.')]);

        return to_route('admin.invoices.edit', $invoice);
    }
}
