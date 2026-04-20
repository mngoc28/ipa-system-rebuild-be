<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

final class CountryFactory extends Factory
{
    protected $model = Country::class;

    public function definition(): array
    {
        $nameVi = $this->faker->randomElement([
            'Việt Nam',
            'Nhật Bản',
            'Hàn Quốc',
            'Singapore',
            'Hoa Kỳ',
            'Đức',
        ]);

        return [
            'code' => 'COUNTRY_' . strtoupper(Str::slug($nameVi)),
            'name_vi' => $nameVi,
            'name_en' => $this->faker->randomElement([
                'Vietnam',
                'Japan',
                'South Korea',
                'Singapore',
                'United States',
                'Germany',
            ]),
            'is_active' => true,
        ];
    }
}