<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

final class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $startAt = $this->faker->dateTimeBetween('+1 day', '+45 days');
        $endAt = (clone $startAt)->modify('+' . $this->faker->numberBetween(60, 180) . ' minutes');

        return [
            'delegation_id' => null,
            'title' => $this->faker->randomElement([
                'Làm việc với Samsung Electronics',
                'Khảo sát Khu công nghệ cao',
                'Hội thảo xúc tiến đầu tư',
                'Gặp gỡ đối tác logistics',
                'Trao đổi chính sách ưu đãi',
            ]),
            'description' => $this->faker->sentence(16),
            'event_type' => $this->faker->randomElement([1, 2, 3, 4]),
            'status' => $this->faker->randomElement([0, 1]),
            'start_at' => $startAt,
            'end_at' => $endAt,
            'location_id' => null,
            'organizer_user_id' => 1,
        ];
    }
}