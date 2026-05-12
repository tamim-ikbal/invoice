<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\CreatePaymentAction;
use App\Actions\Admin\DeletePaymentAction;
use App\Actions\Admin\UpdatePaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePaymentRequest;
use App\Http\Requests\Admin\UpdatePaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class PaymentController extends Controller
{
    /**
     * Store a newly created payment for the given invoice.
     */
    public function store(StorePaymentRequest $request, Invoice $invoice, CreatePaymentAction $action): RedirectResponse
    {
        $action->handle($invoice, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Payment added.')]);

        return to_route('admin.invoices.edit', $invoice);
    }

    /**
     * Update the specified payment.
     */
    public function update(UpdatePaymentRequest $request, Invoice $invoice, Payment $payment, UpdatePaymentAction $action): RedirectResponse
    {
        $action->handle($payment, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Payment updated.')]);

        return to_route('admin.invoices.edit', $invoice);
    }

    /**
     * Remove the specified payment.
     */
    public function destroy(Invoice $invoice, Payment $payment, DeletePaymentAction $action): RedirectResponse
    {
        $action->handle($payment);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Payment deleted.')]);

        return to_route('admin.invoices.edit', $invoice);
    }
}
