<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaPartnerProjectSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_partner_project')->exists()) {
            return;
        }

        DB::table('ipa_partner_project')->insert([
                'partner_id' => DB::table('ipa_partner')->value('id'),
                'delegation_id' => DB::table('ipa_delegation')->value('id'),
                'project_name' => 'project_name_seed',
                'stage_id' => DB::table('ipa_md_pipeline_stage')->value('id'),
                'estimated_value' => 1.00,
                'success_probability' => 1.00,
                'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
