<?php

namespace App\Concerns;

use App\Models\Adjustment;
use App\Models\Invoice;
use App\Services\Helper;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait AdjustmentValidationRules
{
    /**
     * Get the validation rules for the carried amount.
     *
     * The amount may never exceed what is still unsettled on the source invoice.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function amountRules(Invoice $invoice, ?Adjustment $ignore = null): array
    {
        return [
            'required',
            'numeric',
            'decimal:0,2',
            'min:0.01',
            'max:'.$this->remainingBalance($invoice, $ignore),
        ];
    }

    /**
     * Get the validation rules for the invoice receiving the balance.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function targetInvoiceRules(Invoice $invoice): array
    {
        return [
            'nullable',
            'integer',
            Rule::exists('invoices', 'id')->whereNull('deleted_at'),
            Rule::notIn([$invoice->id]),
            fn (string $attribute, mixed $value, Closure $fail) => $this->validateTarget($invoice, (int) $value, $fail),
        ];
    }

    /**
     * Get the validation rules for the adjustment note.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function noteRules(): array
    {
        return ['nullable', 'string', 'max:255'];
    }

    /**
     * Calculate what is still unsettled on the given invoice.
     *
     * Any adjustment being edited is excluded so re-saving an unchanged amount still passes.
     */
    protected function remainingBalance(Invoice $invoice, ?Adjustment $ignore = null): float
    {
        $invoice->loadMissing(['items', 'payments', 'adjustments', 'adjustmentsIn']);

        $otherCarried = $invoice->adjustments
            ->when($ignore, fn ($adjustments) => $adjustments->where('id', '!=', $ignore->id))
            ->sum('amount');

        return max(0, Helper::roundAmount(
            $invoice->total_amount - $invoice->paid_amount - $otherCarried
        ));
    }

    /**
     * Reject targets that belong to another client or would create a cycle.
     */
    protected function validateTarget(Invoice $invoice, int $targetId, Closure $fail): void
    {
        $target = Invoice::find($targetId);

        if (! $target) {
            return;
        }

        if ($invoice->client_id && $target->client_id !== $invoice->client_id) {
            $fail(__('A balance can only be carried to an invoice for the same client.'));

            return;
        }

        $cycles = Adjustment::where('source_invoice_id', $target->id)
            ->where('target_invoice_id', $invoice->id)
            ->exists();

        if ($cycles) {
            $fail(__('That invoice already carries a balance to this one.'));
        }
    }
}
