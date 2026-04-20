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

        $userId = DB::table('ipa_user')->value('id');

        DB::table('ipa_auth_session')->insert([
            'user_id' => $userId,
            'access_token_jti' => 'auth-session-jti-0001',
            'refresh_token_hash' => 'auth-session-refresh-hash-0001',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'issued_at' => now(),
            'expires_at' => now()->addDay(),
            'revoked_at' => now()->addHours(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
