<?php

namespace App\DTOs\Admin;

use App\Enums\PaymentStatusEnum;
use App\Http\Requests\Admin\StorePaymentRequest;
use App\Http\Requests\Admin\UpdatePaymentRequest;
use App\Services\Helper;

final readonly class PaymentData
{
    public function __construct(
        public ?string $title,
        public float $amount,
        public string $date,
        public PaymentStatusEnum $status,
        public string $paymentMethod,
        public ?float $bdtRate,
    ) {}

    public static function fromRequest(StorePaymentRequest|UpdatePaymentRequest $request): self
    {
        $data = $request->validated();

        return new self(
            title: $data['title'] ?? null,
            amount: Helper::roundAmount($data['amount']),
            date: $data['date'] ?? now()->toDateString(),
            status: PaymentStatusEnum::from($data['status']),
            paymentMethod: $data['payment_method'],
            bdtRate: isset($data['bdt_rate']) ? (float) $data['bdt_rate'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'amount' => $this->amount,
            'date' => $this->date,
            'status' => $this->status->value,
            'payment_method' => $this->paymentMethod,
            'bdt_rate' => $this->bdtRate,
        ];
    }
}
