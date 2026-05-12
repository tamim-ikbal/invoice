<?php

namespace App\Http\Requests\Admin;

use App\Concerns\InvoiceValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    use InvoiceValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => $this->titleRules(),
            'client_id' => $this->clientRules(),
            'status' => $this->statusRules(),
            'date' => $this->dateRules(),
        ];
    }
}
