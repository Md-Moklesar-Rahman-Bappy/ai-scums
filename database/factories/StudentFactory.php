<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Student factory.
 *
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'admission_no' => $this->faker->unique()->numerify('ADM#####'),
            'roll_no' => $this->faker->numberBetween(1, 60),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'date_of_birth' => $this->faker->dateTimeBetween('-18 years', '-5 years'),
            'blood_group' => $this->faker->randomElement(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-']),
            'guardian_name' => $this->faker->name(),
            'guardian_phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'admission_date' => $this->faker->dateTimeBetween('-3 years', '-1 month'),
            'status' => 'active',
        ];
    }
}
