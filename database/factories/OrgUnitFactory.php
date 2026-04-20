<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OrgUnit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

final class OrgUnitFactory extends Factory
{
    protected $model = OrgUnit::class;

    public function definition(): array
    {
        $unitName = $this->faker->randomElement([
            'Phòng Xúc tiến đầu tư',
            'Phòng Hỗ trợ doanh nghiệp',
            'Phòng Phân tích thị trường',
            'Phòng Dịch vụ một cửa',
            'Tổ công tác FDI',
            'Tổ công tác công nghệ cao',
        ]);

        return [
            'unit_code' => 'UNIT_' . strtoupper(Str::slug($unitName)) . '_' . $this->faker->unique()->numberBetween(100, 999),
            'unit_name' => $unitName,
            'unit_type' => $this->faker->randomElement(['DEPARTMENT', 'DIVISION', 'TEAM']),
            'parent_unit_id' => null,
            'manager_user_id' => null,
        ];
    }
}