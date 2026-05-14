<?php

namespace Database\Factories;

use App\Models\InvoiceViewLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceViewLog>
 */
class InvoiceViewLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ip' => fake()->ipv4(),
            'browser' => fake()->userAgent(),
            'country' => fake()->country(),
            'viewed_at' => fake()->dateTimeBetween('-30 days'),
        ];
    }
}
