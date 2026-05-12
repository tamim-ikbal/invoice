<?php

namespace App\DTOs\Admin;

use App\Http\Requests\Admin\StoreClientRequest;
use App\Http\Requests\Admin\UpdateClientRequest;

final readonly class ClientData
{
    public function __construct(
        public string $name,
        public string $email,
    ) {}

    public static function fromRequest(StoreClientRequest|UpdateClientRequest $request): self
    {
        $data = $request->validated();

        return new self(
            name: $data['name'],
            email: $data['email'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
