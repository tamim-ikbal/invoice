<?php

namespace App\DTOs\Admin;

use App\Http\Requests\Admin\StorePaymentRequest;
use App\Http\Requests\Admin\UpdatePaymentRequest;
use App\Services\Helper;

final readonly class PaymentData
{
    public function __construct(
        public ?string $title,
        public float $amount,
        public string $date,
        public string $status,
        public string $paymentMethod,
    ) {}

    public static function fromRequest(StorePaymentRequest|UpdatePaymentRequest $request): self
    {
        $data = $request->validated();

        return new self(
            title: $data['title'] ?? null,
            amount: Helper::roundAmount($data['amount']),
            date: $data['date'] ?? now()->toDateString(),
            status: $data['status'],
            paymentMethod: $data['payment_method'],
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
            'status' => $this->status,
            'payment_method' => $this->paymentMethod,
        ];
    }
}
