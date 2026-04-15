<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaLocationSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_location')->exists()) {
            return;
        }

        DB::table('ipa_location')->insert([
                'name' => 'name_seed',
                'address_line' => 'address_line_seed',
                'ward' => 'ward_seed',
                'district' => 'district_seed',
                'city' => 'city_seed',
                'country_id' => DB::table('ipa_country')->value('id'),
                'lat' => 1.00,
                'lng' => 1.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
