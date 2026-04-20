<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaMdDelegationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['code' => 'INBOUND', 'name_vi' => 'Đón đoàn', 'sort_order' => 1],
            ['code' => 'OUTBOUND', 'name_vi' => 'Đi xúc tiến', 'sort_order' => 2],
        ];

        foreach ($rows as $row) {
            DB::table('ipa_md_delegation_type')->updateOrInsert(
                ['code' => $row['code']],
                [
                    'name_vi' => $row['name_vi'],
                    'is_active' => true,
                    'sort_order' => $row['sort_order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
