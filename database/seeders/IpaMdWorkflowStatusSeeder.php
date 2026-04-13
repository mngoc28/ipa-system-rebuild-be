<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaMdWorkflowStatusSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_md_workflow_status')->exists()) {
            return;
        }

        DB::table('ipa_md_workflow_status')->insert([
                'domain_code' => 'IPA_MD_WORKFLOW_STATUS_CODE',
                'code' => 'IPA_MD_WORKFLOW_STATUS_CODE',
                'name_vi' => 'name_vi_seed',
                'sort_order' => 1,
                'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
