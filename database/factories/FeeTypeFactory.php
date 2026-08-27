<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FeeType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * FeeType factory.
 *
 * @extends Factory<FeeType>
 */
class FeeTypeFactory extends Factory
{
    protected $model = FeeType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Tuition', 'Exam Fee', 'Transport', 'Library', 'Lab']),
            'description' => $this->faker->sentence(4),
            'default_amount' => $this->faker->numberBetween(500, 5000),
        ];
    }
}
