<?php

namespace App\DTOs\Admin;

use App\Http\Requests\Admin\UpdateInvoiceSettingsRequest;

final readonly class UpdateInvoiceSettingsData
{
    public function __construct(
        public bool $showQuantity,
    ) {}

    public static function fromRequest(UpdateInvoiceSettingsRequest $request): self
    {
        $data = $request->validated();

        return new self(
            showQuantity: $data['show_quantity'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'show_quantity' => $this->showQuantity,
        ];
    }
}
