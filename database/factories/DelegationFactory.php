<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Delegation;
use Illuminate\Database\Eloquent\Factories\Factory;

final class DelegationFactory extends Factory
{
    protected $model = Delegation::class;

    public function definition(): array
    {
        $name = $this->faker->randomElement([
            'Đón đoàn Samsung Hàn Quốc',
            'Xúc tiến đầu tư Fintech Singapore',
            'Làm việc với đối tác Nhật Bản',
            'Khảo sát chuỗi cung ứng bán dẫn',
            'Hội nghị xúc tiến logistics',
            'Trao đổi năng lượng tái tạo',
        ]);

        $startDate = $this->faker->dateTimeBetween('+3 days', '+30 days');
        $durationDays = $this->faker->numberBetween(2, 5);

        return [
            'code' => 'DEL-' . now()->format('Y') . '-' . $this->faker->unique()->numberBetween(100, 999),
            'name' => $name,
            'direction' => $this->faker->randomElement([1, 2]),
            'status' => $this->faker->randomElement([1, 2, 3]),
            'priority' => $this->faker->randomElement([1, 2, 3]),
            'country_id' => \App\Models\Country::factory(),
            'host_unit_id' => \App\Models\OrgUnit::factory(),
            'owner_user_id' => \App\Models\AdminUser::factory(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => (clone $startDate)->modify('+' . ($durationDays - 1) . ' days')->format('Y-m-d'),
            'participant_count' => $this->faker->numberBetween(5, 15),
            'objective' => $this->faker->randomElement([
                'Khảo sát môi trường đầu tư và nhu cầu mở rộng.',
                'Thúc đẩy hợp tác trong lĩnh vực công nghệ cao.',
                'Trao đổi chính sách hỗ trợ và ưu đãi đầu tư.',
                'Kết nối đối tác chiến lược với hệ sinh thái Đà Nẵng.',
            ]),
            'description' => $this->faker->sentence(18),
        ];
    }
}