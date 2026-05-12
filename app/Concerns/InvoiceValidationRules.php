<?php

namespace App\Concerns;

use App\Enums\InvoiceStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait InvoiceValidationRules
{
    /**
     * Get the validation rules for the invoice title.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function titleRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules for the invoice client.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function clientRules(): array
    {
        return ['nullable', 'exists:clients,id'];
    }

    /**
     * Get the validation rules for the invoice status.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function statusRules(): array
    {
        return ['required', Rule::enum(InvoiceStatusEnum::class)];
    }

    /**
     * Get the validation rules for the invoice date.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function dateRules(): array
    {
        return ['required', 'date'];
    }

    /**
     * Get the validation rules for invoice tasks.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function taskRules(): array
    {
        return [
            'tasks' => ['array'],
            'tasks.*.name' => ['required', 'string'],
            'tasks.*.amount' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get the validation rules for invoice payments.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function paymentRules(): array
    {
        return [
            'payments' => ['array'],
            'payments.*.amount' => ['required', 'numeric', 'min:0'],
            'payments.*.date' => ['required', 'date'],
            'payments.*.status' => ['required', Rule::enum(PaymentStatusEnum::class)],
            'payments.*.payment_method' => ['required', Rule::enum(PaymentMethodEnum::class)],
        ];
    }
}
