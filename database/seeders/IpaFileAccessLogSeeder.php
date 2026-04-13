<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaFileAccessLogSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_file_access_log')->exists()) {
            return;
        }

        DB::table('ipa_file_access_log')->insert([
                'file_id' => DB::table('ipa_file')->value('id'),
                'user_id' => DB::table('ipa_user')->value('id'),
                'action' => 1,
                'ip_address' => 'ip_address_seed',
                'action_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
