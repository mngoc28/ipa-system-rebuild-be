<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaMdPrioritySeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_md_priority')->exists()) {
            return;
        }

        DB::table('ipa_md_priority')->insert([
                'code' => 'IPA_MD_PRIORITY_CODE',
                'name_vi' => 'name_vi_seed',
                'weight' => 1,
                'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
