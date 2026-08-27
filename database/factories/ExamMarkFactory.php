<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ExamMark;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * ExamMark factory.
 *
 * @extends Factory<ExamMark>
 */
class ExamMarkFactory extends Factory
{
    protected $model = ExamMark::class;

    public function definition(): array
    {
        $obtained = $this->faker->numberBetween(20, 100);

        return [
            'marks_obtained' => $obtained,
            'total_marks' => 100,
            'grade' => ExamMark::deriveGrade((float) $obtained, 100.0),
            'remarks' => null,
        ];
    }
}
