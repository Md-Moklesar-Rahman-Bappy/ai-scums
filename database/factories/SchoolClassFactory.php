<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * SchoolClass factory.
 *
 * @extends Factory<SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;

    public function definition(): array
    {
        return [
            'name' => 'Grade '.$this->faker->numberBetween(1, 12),
        ];
    }
}
