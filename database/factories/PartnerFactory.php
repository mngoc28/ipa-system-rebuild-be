<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

final class PartnerFactory extends Factory
{
    protected $model = Partner::class;

    public function definition(): array
    {
        $partnerName = $this->faker->randomElement([
            'FPT Corporation',
            'Samsung Electronics',
            'Intel Corporation',
            'Nidec Vietnam',
            'Toyota Motor Vietnam',
            'LG Electronics',
            'Daikin Industries',
            'Mitsubishi Corporation',
            'Panasonic Vietnam',
            'Nissan Vietnam',
            'AEON Vietnam',
            'Sun Group',
            'VinFast',
            'Shopee Vietnam',
            'Mekong Capital',
        ]);

        return [
            'partner_code' => 'PARTNER_' . strtoupper(Str::slug($partnerName)) . '_' . $this->faker->unique()->numberBetween(100, 999),
            'partner_name' => $partnerName,
            'country_id' => 1,
            'sector_id' => 1,
            'status' => $this->faker->randomElement([0, 1, 2]),
            'score' => $this->faker->randomFloat(2, 2.50, 5.00),
            'website' => 'https://www.' . Str::slug($partnerName) . '.com',
            'notes' => $this->faker->randomElement([
                'Đối tác chiến lược tại Đà Nẵng.',
                'Đang trao đổi cơ hội đầu tư mở rộng.',
                'Ưu tiên theo dõi tiến độ xúc tiến.',
                'Có nhu cầu khảo sát địa điểm và chính sách ưu đãi.',
            ]),
        ];
    }
}