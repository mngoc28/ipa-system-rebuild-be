<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaCountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['code' => 'COUNTRY_VIETNAM', 'name_vi' => 'Việt Nam', 'name_en' => 'Vietnam'],
            ['code' => 'COUNTRY_JAPAN', 'name_vi' => 'Nhật Bản', 'name_en' => 'Japan'],
            ['code' => 'COUNTRY_KOREA', 'name_vi' => 'Hàn Quốc', 'name_en' => 'South Korea'],
            ['code' => 'COUNTRY_SINGAPORE', 'name_vi' => 'Singapore', 'name_en' => 'Singapore'],
            ['code' => 'COUNTRY_USA', 'name_vi' => 'Hoa Kỳ', 'name_en' => 'United States'],
            ['code' => 'COUNTRY_GERMANY', 'name_vi' => 'Đức', 'name_en' => 'Germany'],
            ['code' => 'COUNTRY_TAIWAN', 'name_vi' => 'Đài Loan', 'name_en' => 'Taiwan'],
        ];

        foreach ($countries as $country) {
            DB::table('ipa_country')->updateOrInsert(
                ['code' => $country['code']],
                [
                    ...$country,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
