<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaFileShareSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_file_share')->exists()) {
            return;
        }

        DB::table('ipa_file_share')->insert([
                'file_id' => DB::table('ipa_file')->value('id'),
                'shared_with_user_id' => DB::table('ipa_user')->value('id'),
                'shared_with_role_id' => DB::table('ipa_role')->value('id'),
                'permission_level' => 1,
                'expires_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
