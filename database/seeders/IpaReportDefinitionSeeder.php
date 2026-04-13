<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaReportDefinitionSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_report_definition')->exists()) {
            return;
        }

        DB::table('ipa_report_definition')->insert([
                'report_code' => 'IPA_REPORT_DEFINITION_CODE',
                'report_name' => 'report_name_seed',
                'scope_type' => 1,
                'owner_role_id' => DB::table('ipa_role')->value('id'),
                'query_config' => json_encode(['k' => 'v']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
