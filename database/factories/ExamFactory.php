<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Exam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Exam factory.
 *
 * @extends Factory<Exam>
 */
class ExamFactory extends Factory
{
    protected $model = Exam::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Midterm', 'Final', 'Unit Test', 'Quarterly']),
            'exam_type' => $this->faker->randomElement(['theory', 'practical', 'mixed']),
            'exam_date' => $this->faker->dateTimeBetween('-2 months', '+2 months'),
            'total_marks' => 100,
            'pass_marks' => 40,
        ];
    }
}
