<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Teacher factory.
 *
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        return [
            'employee_id' => $this->faker->unique()->numerify('EMP#####'),
            'designation' => $this->faker->randomElement(['Lecturer', 'Senior Teacher', 'Assistant Professor', 'Teacher']),
            'qualification' => $this->faker->randomElement(['MSc', 'BEd', 'MA', 'MPhil', 'PhD']),
            'joining_date' => $this->faker->dateTimeBetween('-10 years', '-1 year'),
            'status' => 'active',
        ];
    }
}
