<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaCountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['code' => 'COUNTRY_VIETNAM', 'name_vi' => 'Việt Nam', 'name_en' => 'Vietnam'],
            ['code' => 'COUNTRY_JAPAN', 'name_vi' => 'Nhật Bản', 'name_en' => 'Japan'],
            ['code' => 'COUNTRY_KOREA', 'name_vi' => 'Hàn Quốc', 'name_en' => 'South Korea'],
            ['code' => 'COUNTRY_SINGAPORE', 'name_vi' => 'Singapore', 'name_en' => 'Singapore'],
        ];

        foreach ($countries as $country) {
            if (DB::table('ipa_country')->where('code', $country['code'])->exists()) {
                continue;
            }

            DB::table('ipa_country')->insert([
                ...$country,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
