<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaMdPipelineStageSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_md_pipeline_stage')->exists()) {
            return;
        }

        DB::table('ipa_md_pipeline_stage')->insert([
                'code' => 'IPA_MD_PIPELINE_STAGE_CODE',
                'name_vi' => 'name_vi_seed',
                'stage_order' => 1,
                'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
