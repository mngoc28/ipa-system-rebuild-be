<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaUserRoleSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_user_role')->exists()) {
            return;
        }

        DB::table('ipa_user_role')->insert([
                'user_id' => DB::table('ipa_user')->value('id'),
                'role_id' => DB::table('ipa_role')->value('id'),
                'effective_from' => now(),
                'effective_to' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
