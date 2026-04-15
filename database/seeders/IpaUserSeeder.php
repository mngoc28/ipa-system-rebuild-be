<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaUserSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_user')->exists()) {
            return;
        }

        DB::table('ipa_user')->insert([
                'username' => 'username_seed',
                'email' => 'seed_ipa_user@example.com',
                'full_name' => 'full_name_seed',
                'phone' => '0900000000',
                'avatar_url' => 'avatar_url seed text',
                'status' => 1,
                'primary_unit_id' => DB::table('ipa_org_unit')->value('id'),
                'last_login_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
