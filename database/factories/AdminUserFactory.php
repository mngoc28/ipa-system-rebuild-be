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
        $lastNames = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Phan', 'Vũ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương', 'Lý'];
        $middleNames = ['Văn', 'Thị', 'Đình', 'Ngọc', 'Hữu', 'Minh', 'Kim', 'Thành', 'Xuân', 'Hồng', 'Thanh', 'Đức', 'Quang', 'Anh'];
        $firstNames = ['An', 'Bình', 'Cường', 'Dũng', 'Giang', 'Hà', 'Hùng', 'Linh', 'Nam', 'Phượng', 'Quân', 'Sơn', 'Tuấn', 'Việt', 'Xuân', 'Yến', 'Anh', 'Khoa', 'Thịnh', 'Đạt', 'Huy', 'Hoàng', 'Bảo'];

        $fullName = $this->faker->randomElement($lastNames) . ' ' . 
                    $this->faker->randomElement($middleNames) . ' ' . 
                    $this->faker->randomElement($firstNames);

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