<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaMdApprovalStatusSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['code' => 'PENDING', 'name_vi' => 'Chờ phê duyệt', 'is_active' => true],
            ['code' => 'APPROVED', 'name_vi' => 'Đã phê duyệt', 'is_active' => true],
            ['code' => 'REJECTED', 'name_vi' => 'Từ chối', 'is_active' => true],
        ];

        foreach ($rows as $row) {
            $exists = DB::table('ipa_md_approval_status')->where('code', $row['code'])->exists();

            if ($exists) {
                continue;
            }

            DB::table('ipa_md_approval_status')->insert([
                'code' => $row['code'],
                'name_vi' => $row['name_vi'],
                'is_active' => $row['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
