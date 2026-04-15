<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaAuthSessionSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_auth_session')->exists()) {
            return;
        }

        DB::table('ipa_auth_session')->insert([
                'user_id' => DB::table('ipa_user')->value('id'),
                'access_token_jti' => 'access_token_jti_seed',
                'refresh_token_hash' => 'refresh_token_hash_seed',
                'ip_address' => 'ip_address_seed',
                'user_agent' => 'user_agent seed text',
                'issued_at' => now(),
                'expires_at' => now(),
                'revoked_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
