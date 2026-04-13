<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaSystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_system_setting')->exists()) {
            return;
        }

        DB::table('ipa_system_setting')->insert([
                'setting_key' => 'setting_key_seed',
                'setting_group' => 'setting_group_seed',
                'setting_value' => 'setting_value seed text',
                'encrypted_value' => 'encrypted_value seed text',
                'is_secret' => true,
                'updated_by' => DB::table('ipa_user')->value('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
