<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaPartnerContactSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_partner_contact')->exists()) {
            return;
        }

        DB::table('ipa_partner_contact')->insert([
                'partner_id' => DB::table('ipa_partner')->value('id'),
                'full_name' => 'full_name_seed',
                'title' => 'title_seed',
                'email' => 'seed_ipa_partner_contact@example.com',
                'phone' => '0900000000',
                'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
