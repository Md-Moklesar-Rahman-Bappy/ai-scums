<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Routine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Routine factory.
 *
 * @extends Factory<Routine>
 */
class RoutineFactory extends Factory
{
    protected $model = Routine::class;

    public function definition(): array
    {
        return [
            'type' => 'class',
            'day_of_week' => $this->faker->numberBetween(1, 5),
            'start_time' => $this->faker->time('H:i'),
            'end_time' => $this->faker->time('H:i'),
            'room' => $this->faker->numerify('Room ###'),
        ];
    }
}
