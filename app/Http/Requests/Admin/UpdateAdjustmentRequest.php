<?php

namespace App\Http\Requests\Admin;

use App\Concerns\AdjustmentValidationRules;
use App\Models\Adjustment;
use App\Models\Invoice;
use App\Services\Helper;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAdjustmentRequest extends FormRequest
{
    use AdjustmentValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $invoice = $this->invoice();

        return [
            'amount' => $this->amountRules($invoice, $this->adjustment()),
            'target_invoice_id' => $this->targetInvoiceRules($invoice),
            'note' => $this->noteRules(),
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.max' => __('The amount may not exceed the remaining balance of :balance.', [
                'balance' => Helper::moneyFormat($this->remainingBalance($this->invoice(), $this->adjustment())),
            ]),
        ];
    }

    /**
     * Get the invoice the balance is being carried out of.
     */
    protected function invoice(): Invoice
    {
        return $this->route('invoice');
    }

    /**
     * Get the adjustment being updated.
     */
    protected function adjustment(): Adjustment
    {
        return $this->route('adjustment');
    }
}
