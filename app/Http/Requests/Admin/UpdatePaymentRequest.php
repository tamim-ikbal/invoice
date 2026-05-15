<?php

namespace App\Http\Requests\Admin;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'status' => ['required', Rule::enum(PaymentStatusEnum::class)],
            'payment_method' => ['required', Rule::enum(PaymentMethodEnum::class)],
            'bdt_rate' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
