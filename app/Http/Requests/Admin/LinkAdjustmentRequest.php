<?php

namespace App\Http\Requests\Admin;

use App\Concerns\AdjustmentValidationRules;
use App\Models\Adjustment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class LinkAdjustmentRequest extends FormRequest
{
    use AdjustmentValidationRules;

    /**
     * Validate the target invoice taken from the route rather than the payload.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['target_invoice_id' => $this->route('invoice')->id]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'target_invoice_id' => $this->targetInvoiceRules($this->adjustment()->sourceInvoice),
        ];
    }

    /**
     * Get the additional validation callbacks.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->adjustment()->target_invoice_id !== null) {
                    $validator->errors()->add(
                        'target_invoice_id',
                        __('That balance has already been carried to an invoice.')
                    );
                }
            },
        ];
    }

    /**
     * Get the adjustment being linked.
     */
    protected function adjustment(): Adjustment
    {
        return $this->route('adjustment');
    }
}
