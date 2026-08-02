<?php

namespace App\Http\Requests\Admin;

use App\Concerns\AdjustmentValidationRules;
use App\Models\Invoice;
use App\Services\Helper;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAdjustmentRequest extends FormRequest
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
            'amount' => $this->amountRules($invoice),
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
                'balance' => Helper::moneyFormat($this->remainingBalance($this->invoice())),
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
}
