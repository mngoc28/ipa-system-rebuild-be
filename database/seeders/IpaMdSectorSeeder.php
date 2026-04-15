<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaMdSectorSeeder extends Seeder
{
    public function run(): void
    {
        $sectors = [
            ['code' => 'SECTOR_IT', 'name_vi' => 'Công nghệ cao'],
            ['code' => 'SECTOR_LOGISTICS', 'name_vi' => 'Logistics'],
            ['code' => 'SECTOR_FINTECH', 'name_vi' => 'Fintech'],
            ['code' => 'SECTOR_RENEWABLE', 'name_vi' => 'Năng lượng tái tạo'],
        ];

        foreach ($sectors as $sector) {
            if (DB::table('ipa_md_sector')->where('code', $sector['code'])->exists()) {
                continue;
            }

            DB::table('ipa_md_sector')->insert([
                ...$sector,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
