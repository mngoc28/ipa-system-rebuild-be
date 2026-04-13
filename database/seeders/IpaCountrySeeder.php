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
        if (DB::table('ipa_country')->exists()) {
            return;
        }

        DB::table('ipa_country')->insert([
                'code' => 'IPA_COUNTRY_CODE',
                'name_vi' => 'name_vi_seed',
                'name_en' => 'name_en_seed',
                'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
