<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaPipelineProjectSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_pipeline_project')->exists()) {
            return;
        }

        DB::table('ipa_pipeline_project')->insert([
                'project_code' => 'IPA_PIPELINE_PROJECT_CODE',
                'project_name' => 'project_name_seed',
                'partner_id' => DB::table('ipa_partner')->value('id'),
                'country_id' => DB::table('ipa_country')->value('id'),
                'sector_id' => DB::table('ipa_md_sector')->value('id'),
                'stage_id' => DB::table('ipa_md_pipeline_stage')->value('id'),
                'estimated_value' => 1.00,
                'success_probability' => 1.00,
                'expected_close_date' => now()->toDateString(),
                'owner_user_id' => DB::table('ipa_user')->value('id'),
                'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
