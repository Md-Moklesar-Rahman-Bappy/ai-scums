<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Semester factory.
 *
 * @extends Factory<Semester>
 */
class SemesterFactory extends Factory
{
    protected $model = Semester::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['First Semester', 'Second Semester', 'Third Semester']),
            'number' => $this->faker->numberBetween(1, 8),
        ];
    }
}
