<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['code' => 'ADMIN', 'name' => 'Quản trị hệ thống'],
            ['code' => 'LEADER', 'name' => 'Lãnh đạo'],
            ['code' => 'STAFF', 'name' => 'Chuyên viên'],
            ['code' => 'MANAGER', 'name' => 'Quản lý'],
        ];

        foreach ($roles as $role) {
            DB::table('ipa_role')->updateOrInsert(
                ['code' => $role['code']],
                [
                    ...$role,
                    'is_system' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
