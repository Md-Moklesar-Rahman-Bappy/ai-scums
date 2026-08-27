<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Fee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Fee factory.
 *
 * @extends Factory<Fee>
 */
class FeeFactory extends Factory
{
    protected $model = Fee::class;

    public function definition(): array
    {
        $amount = $this->faker->numberBetween(1000, 8000);

        return [
            'amount' => $amount,
            'paid_amount' => 0,
            'due_date' => $this->faker->dateTimeBetween('now', '+3 months'),
            'status' => 'pending',
        ];
    }
}
