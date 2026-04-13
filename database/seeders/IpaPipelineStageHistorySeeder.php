<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaPipelineStageHistorySeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_pipeline_stage_history')->exists()) {
            return;
        }

        DB::table('ipa_pipeline_stage_history')->insert([
                'pipeline_project_id' => DB::table('ipa_pipeline_project')->value('id'),
                'old_stage_id' => DB::table('ipa_md_pipeline_stage')->value('id'),
                'new_stage_id' => DB::table('ipa_md_pipeline_stage')->value('id'),
                'changed_by' => DB::table('ipa_user')->value('id'),
                'changed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
