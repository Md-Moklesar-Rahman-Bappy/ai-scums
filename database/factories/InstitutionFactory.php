<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Institution factory.
 *
 * @extends Factory<Institution>
 */
class InstitutionFactory extends Factory
{
    protected $model = Institution::class;

    public function definition(): array
    {
        $name = $this->faker->company().' '.$this->faker->randomElement(['School', 'College', 'University']);

        return [
            'name' => $name,
            'type' => $this->faker->randomElement(['school', 'college', 'university']),
            'slug' => $this->faker->unique()->slug(2),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'website' => $this->faker->url(),
            'is_active' => true,
        ];
    }
}
