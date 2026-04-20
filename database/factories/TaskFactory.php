<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

final class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'delegation_id' => null,
            'event_id' => null,
            'minutes_id' => null,
            'title' => $this->faker->randomElement([
                'Rà soát hồ sơ nhà đầu tư',
                'Soạn nội dung làm việc với đối tác',
                'Cập nhật tiến độ sau buổi khảo sát',
                'Gửi email xác nhận lịch họp',
                'Bổ sung tài liệu thuyết minh',
            ]),
            'description' => $this->faker->sentence(18),
            'status' => $this->faker->randomElement([0, 1, 2]),
            'priority' => $this->faker->randomElement([1, 2, 3]),
            'due_at' => $this->faker->dateTimeBetween('-10 days', '+20 days'),
            'is_overdue_cache' => false,
            'created_by' => 1,
        ];
    }
}