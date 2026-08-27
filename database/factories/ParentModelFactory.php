<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ParentModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * ParentModel factory.
 *
 * @extends Factory<ParentModel>
 */
class ParentModelFactory extends Factory
{
    protected $model = ParentModel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'relation' => $this->faker->randomElement(['father', 'mother', 'guardian']),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'address' => $this->faker->address(),
            'occupation' => $this->faker->jobTitle(),
        ];
    }
}
