<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Subject factory.
 *
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Mathematics', 'English', 'Science', 'Social Studies',
                'Physics', 'Chemistry', 'Biology', 'Computer Science',
                'History', 'Geography', 'Economics', 'Accounting',
            ]),
            'code' => $this->faker->unique()->lexify('SUB???'),
            'type' => 'subject',
            'credit_hours' => $this->faker->numberBetween(1, 4),
        ];
    }
}
