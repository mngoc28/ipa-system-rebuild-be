<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AdminUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

final class AdminUserFactory extends Factory
{
    protected $model = AdminUser::class;

    public function definition(): array
    {
        $fullName = $this->faker->randomElement([
            'Nguyễn Văn An',
            'Trần Thị Bích',
            'Lê Minh Tuấn',
            'Phạm Quốc Khánh',
            'Võ Thị Hồng',
            'Đặng Anh Khoa',
            'Hoàng Thu Hà',
            'Bùi Thanh Sơn',
            'Nguyễn Thảo Vy',
            'Trịnh Gia Huy',
        ]);

        $username = Str::slug($fullName) . '-' . $this->faker->unique()->numberBetween(100, 9999);

        return [
            'username' => $username,
            'email' => $username . '@ipa-danang.gov.vn',
            'full_name' => $fullName,
            'phone' => '09' . $this->faker->numerify('########'),
            'avatar_url' => null,
            'status' => 1,
            'primary_unit_id' => null,
            'last_login_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
}