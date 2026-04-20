<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaMdPrioritySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['code' => 'LOW', 'name_vi' => 'Thấp', 'weight' => 1],
            ['code' => 'NORMAL', 'name_vi' => 'Bình thường', 'weight' => 2],
            ['code' => 'HIGH', 'name_vi' => 'Cao', 'weight' => 3],
        ];

        foreach ($rows as $row) {
            DB::table('ipa_md_priority')->updateOrInsert(
                ['code' => $row['code']],
                [
                    'name_vi' => $row['name_vi'],
                    'weight' => $row['weight'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
