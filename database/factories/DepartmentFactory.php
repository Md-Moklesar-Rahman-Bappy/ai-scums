<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Department factory.
 *
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Sciences', 'Arts', 'Commerce', 'Engineering', 'Humanities']),
            'code' => $this->faker->unique()->lexify('DEP???'),
            'description' => $this->faker->sentence(5),
        ];
    }
}
