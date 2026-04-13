<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaMdTaskStatusSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_md_task_status')->exists()) {
            return;
        }

        DB::table('ipa_md_task_status')->insert([
                'code' => 'IPA_MD_TASK_STATUS_CODE',
                'name_vi' => 'name_vi_seed',
                'is_terminal' => true,
                'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
