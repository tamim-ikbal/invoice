<?php

namespace App\Concerns;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ClientValidationRules
{
    /**
     * Get the validation rules for the client name.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function clientNameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules for the client email.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function clientEmailRules(?int $ignoreId = null): array
    {
        return [
            'required',
            'email',
            $ignoreId === null
                ? Rule::unique('clients', 'email')
                : Rule::unique('clients', 'email')->ignore($ignoreId),
        ];
    }
}
