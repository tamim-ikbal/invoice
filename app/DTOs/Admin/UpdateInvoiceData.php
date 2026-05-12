<?php

namespace App\DTOs\Admin;

use App\Http\Requests\Admin\UpdateInvoiceRequest;

final readonly class UpdateInvoiceData
{
    public function __construct(
        public string $title,
        public ?int $clientId,
        public string $status,
        public ?string $date,
    ) {}

    public static function fromRequest(UpdateInvoiceRequest $request): self
    {
        $data = $request->validated();

        return new self(
            title: $data['title'],
            clientId: $data['client_id'] ?? null,
            status: $data['status'],
            date: $data['date'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'client_id' => $this->clientId,
            'status' => $this->status,
            'date' => $this->date,
        ];
    }
}
