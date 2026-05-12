<?php

namespace App\DTOs\Admin;

use App\Http\Requests\Admin\StoreInvoiceItemRequest;
use App\Http\Requests\Admin\UpdateInvoiceItemRequest;
use App\Services\Helper;

final readonly class InvoiceItemData
{
    public function __construct(
        public string $name,
        public int $quantity,
        public float $amount,
    ) {}

    public static function fromRequest(StoreInvoiceItemRequest|UpdateInvoiceItemRequest $request): self
    {
        $data = $request->validated();

        return new self(
            name: $data['name'],
            quantity: $data['quantity'] ?? 1,
            amount: Helper::roundAmount($data['amount']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'quantity' => $this->quantity,
            'amount' => $this->amount,
        ];
    }
}
