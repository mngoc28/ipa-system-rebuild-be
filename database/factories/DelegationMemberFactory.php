<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DelegationMember;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

final class DelegationMemberFactory extends Factory
{
    protected $model = DelegationMember::class;

    public function definition(): array
    {
        $fullName = $this->faker->randomElement([
            'Nguyễn Văn Minh',
            'Trần Thị Lan',
            'Lê Minh Tuấn',
            'Phạm Quốc Huy',
            'Võ Thị Hạnh',
            'Đặng Tuấn Kiệt',
            'Hoàng Thị Kim',
            'Bùi Anh Dũng',
        ]);

        return [
            'delegation_id' => 1,
            'full_name' => $fullName,
            'title' => $this->faker->randomElement(['Giám đốc', 'Phó giám đốc', 'Chuyên viên', 'Trưởng phòng']),
            'organization_name' => $this->faker->randomElement([
                'FPT',
                'Samsung',
                'Intel',
                'Nidec',
                'UBND TP Đà Nẵng',
                'Khu CNC Đà Nẵng',
            ]),
            'contact_email' => Str::slug($fullName) . '@ipa-danang.gov.vn',
            'contact_phone' => '09' . $this->faker->numerify('########'),
            'member_type' => $this->faker->randomElement([0, 1]),
        ];
    }
}