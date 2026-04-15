<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaUserSeeder extends Seeder
{
    public function run(): void
    {
        $unitId = DB::table('ipa_org_unit')->value('id');

        $users = [
            [
                'username' => 'admin',
                'email' => 'admin@danang.gov.vn',
                'full_name' => 'Nguyễn Văn Quản Trị',
                'phone' => '0905000001',
                'avatar_url' => null,
                'status' => 1,
                'primary_unit_id' => $unitId,
                'last_login_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'director',
                'email' => 'director@danang.gov.vn',
                'full_name' => 'Hồ Kỳ Minh',
                'phone' => '0905000002',
                'avatar_url' => null,
                'status' => 1,
                'primary_unit_id' => $unitId,
                'last_login_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'manager',
                'email' => 'manager@danang.gov.vn',
                'full_name' => 'Nguyễn Minh Châu',
                'phone' => '0905000003',
                'avatar_url' => null,
                'status' => 1,
                'primary_unit_id' => $unitId,
                'last_login_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'staff',
                'email' => 'staff@danang.gov.vn',
                'full_name' => 'Trần Thu Hà',
                'phone' => '0905000004',
                'avatar_url' => null,
                'status' => 1,
                'primary_unit_id' => $unitId,
                'last_login_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            DB::table('ipa_user')->updateOrInsert(
                ['username' => $user['username']],
                $user
            );
        }
    }
}
