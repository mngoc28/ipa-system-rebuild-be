<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaMdMinutesStatusSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_md_minutes_status')->exists()) {
            return;
        }

        DB::table('ipa_md_minutes_status')->insert([
            ['code' => 'DRAFT', 'name_vi' => 'Bản nháp', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'INTERNAL', 'name_vi' => 'Nội bộ', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'FINAL', 'name_vi' => 'Đã ký', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
