<?php

namespace App\DTOs\Admin;

use App\Http\Requests\Admin\CreateInvoiceRequest;

final readonly class CreateInvoiceData
{
    public function __construct(
        public string $title,
    ) {}

    public static function fromRequest(CreateInvoiceRequest $request): self
    {
        return new self(
            title: $request->validated('title'),
        );
    }
}
