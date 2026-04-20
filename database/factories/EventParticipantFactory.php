<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EventParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;

final class EventParticipantFactory extends Factory
{
    protected $model = EventParticipant::class;

    public function definition(): array
    {
        return [
            'event_id' => 1,
            'user_id' => 1,
            'participation_status' => $this->faker->randomElement([0, 1, 2]),
            'invited_at' => $this->faker->dateTimeBetween('-14 days', 'now'),
        ];
    }
}