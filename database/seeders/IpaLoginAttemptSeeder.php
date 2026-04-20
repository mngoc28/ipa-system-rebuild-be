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

        $username = DB::table('ipa_user')->value('email') ?? 'admin@ipa.danang.gov.vn';

        DB::table('ipa_login_attempt')->insert([
            'username_or_email' => $username,
            'ip_address' => '127.0.0.1',
            'is_success' => true,
            'reason' => 'Đăng nhập hợp lệ cho tài khoản quản trị.',
            'attempted_at' => now()->subMinutes(15),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
