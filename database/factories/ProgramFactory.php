<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Program factory.
 *
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    protected $model = Program::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['BSc Computer Science', 'BA English', 'BCom', 'MSc Physics']),
            'code' => $this->faker->unique()->lexify('PRG???'),
            'degree' => $this->faker->randomElement(['Bachelor', 'Master', 'Diploma']),
        ];
    }
}
