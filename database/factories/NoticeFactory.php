<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Notice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Notice factory.
 *
 * @extends Factory<Notice>
 */
class NoticeFactory extends Factory
{
    protected $model = Notice::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'body' => $this->faker->paragraph(3),
            'type' => $this->faker->randomElement(['announcement', 'event', 'notification']),
            'audience' => $this->faker->randomElement(['all', 'students', 'teachers', 'parents']),
            'published_at' => $this->faker->dateTimeBetween('-10 days', 'now'),
            'expires_at' => $this->faker->dateTimeBetween('+1 day', '+30 days'),
        ];
    }
}
