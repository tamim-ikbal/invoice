<?php

namespace Database\Factories;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->optional()->sentence(3),
            'amount' => fake()->randomFloat(2, 50, 500),
            'date' => fake()->date(),
            'status' => PaymentStatusEnum::UNPAID,
            'payment_method' => PaymentMethodEnum::PAYONEER,
        ];
    }

    /**
     * Indicate that the payment is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatusEnum::PAID,
        ]);
    }
}
