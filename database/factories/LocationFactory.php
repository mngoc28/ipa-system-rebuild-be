<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

final class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        $venues = [
            'Khu CNC Đà Nẵng',
            'Furama Resort',
            'UBND TP Đà Nẵng',
            'Sân bay quốc tế Đà Nẵng',
            'Cảng Tiên Sa',
            'Bến du thuyền Sơn Trà',
            'InterContinental Danang Sun Peninsula',
            'Trung tâm Hành chính Đà Nẵng',
        ];

        $name = $this->faker->randomElement($venues);

        return [
            'name' => $name,
            'address_line' => $this->faker->streetAddress(),
            'ward' => $this->faker->randomElement(['Hòa Hải', 'Hòa Xuân', 'Thạch Thang', 'An Hải Bắc', 'Hải Châu 1']),
            'district' => $this->faker->randomElement(['Hải Châu', 'Sơn Trà', 'Ngũ Hành Sơn', 'Liên Chiểu']),
            'city' => 'Đà Nẵng',
            'country_id' => null,
            'lat' => $this->faker->randomFloat(7, 15.95, 16.15),
            'lng' => $this->faker->randomFloat(7, 107.95, 108.30),
        ];
    }
}