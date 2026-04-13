<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaPartnerSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_partner')->exists()) {
            return;
        }

        DB::table('ipa_partner')->insert([
                'partner_code' => 'IPA_PARTNER_CODE',
                'partner_name' => 'partner_name_seed',
                'country_id' => DB::table('ipa_country')->value('id'),
                'sector_id' => DB::table('ipa_md_sector')->value('id'),
                'status' => 1,
                'score' => 1.00,
                'website' => 'website seed text',
                'notes' => 'notes seed text',
                'deleted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
