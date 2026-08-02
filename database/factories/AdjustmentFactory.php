<?php

namespace Database\Factories;

use App\Models\Adjustment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Adjustment>
 */
class AdjustmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => fake()->randomFloat(2, 10, 200),
            'note' => fake()->optional()->sentence(3),
        ];
    }

    /**
     * Indicate that the adjustment has not been linked to a target invoice yet.
     */
    public function unapplied(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_invoice_id' => null,
        ]);
    }
}
