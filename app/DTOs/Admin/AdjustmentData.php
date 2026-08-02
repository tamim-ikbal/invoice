<?php

namespace App\DTOs\Admin;

use App\Http\Requests\Admin\StoreAdjustmentRequest;
use App\Http\Requests\Admin\UpdateAdjustmentRequest;
use App\Services\Helper;

final readonly class AdjustmentData
{
    public function __construct(
        public float $amount,
        public ?int $targetInvoiceId,
        public ?string $note,
    ) {}

    public static function fromRequest(StoreAdjustmentRequest|UpdateAdjustmentRequest $request): self
    {
        $data = $request->validated();

        return new self(
            amount: Helper::roundAmount($data['amount']),
            targetInvoiceId: isset($data['target_invoice_id']) ? (int) $data['target_invoice_id'] : null,
            note: $data['note'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'target_invoice_id' => $this->targetInvoiceId,
            'note' => $this->note,
        ];
    }
}
