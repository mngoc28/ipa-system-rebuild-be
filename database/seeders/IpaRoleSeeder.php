<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaRoleSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_role')->exists()) {
            return;
        }

        DB::table('ipa_role')->insert([
                'code' => 'IPA_ROLE_CODE',
                'name' => 'name_seed',
                'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
