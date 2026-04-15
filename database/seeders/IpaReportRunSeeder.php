<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaReportRunSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_report_run')->exists()) {
            return;
        }

        DB::table('ipa_report_run')->insert([
                'report_definition_id' => DB::table('ipa_report_definition')->value('id'),
                'run_by' => DB::table('ipa_user')->value('id'),
                'params_json' => json_encode(['seed' => true]),
                'output_file_id' => DB::table('ipa_file')->value('id'),
                'status' => 1,
                'started_at' => now(),
                'finished_at' => now(),
                'error_message' => 'error_message seed text',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
