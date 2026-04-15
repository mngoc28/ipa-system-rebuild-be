<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaLoginAttemptSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_login_attempt')->exists()) {
            return;
        }

        DB::table('ipa_login_attempt')->insert([
                'username_or_email' => 'seed_ipa_login_attempt@example.com',
                'ip_address' => 'ip_address_seed',
                'is_success' => true,
                'reason' => 'reason_seed',
                'attempted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
