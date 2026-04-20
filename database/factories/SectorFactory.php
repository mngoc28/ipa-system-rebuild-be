<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

final class SectorFactory extends Factory
{
    protected $model = Sector::class;

    public function definition(): array
    {
        $nameVi = $this->faker->randomElement([
            'Công nghệ cao',
            'Logistics',
            'Bán dẫn',
            'Fintech',
            'Năng lượng tái tạo',
            'Sản xuất thông minh',
        ]);

        return [
            'code' => 'SECTOR_' . strtoupper(Str::slug($nameVi)),
            'name_vi' => $nameVi,
            'is_active' => true,
        ];
    }
}