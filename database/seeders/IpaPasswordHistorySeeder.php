<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaPasswordHistorySeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_password_history')->exists()) {
            return;
        }

        DB::table('ipa_password_history')->insert([
                'user_id' => DB::table('ipa_user')->value('id'),
                'password_hash' => Hash::make('Password@123'),
                'changed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
