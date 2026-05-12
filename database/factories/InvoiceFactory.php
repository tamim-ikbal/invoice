<?php

namespace Database\Factories;

use App\Enums\InvoiceStatusEnum;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uid' => uniqid(),
            'title' => fake()->sentence(3),
            'status' => InvoiceStatusEnum::DRAFT,
            'date' => fake()->date(),
        ];
    }

    /**
     * Indicate that the invoice is sent.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatusEnum::SENT,
        ]);
    }

    /**
     * Indicate that the invoice is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatusEnum::PAID,
        ]);
    }
}
