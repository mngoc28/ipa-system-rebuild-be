<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaKpiSnapshotSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_kpi_snapshot')->exists()) {
            return;
        }

        DB::table('ipa_kpi_snapshot')->insert([
                'metric_id' => DB::table('ipa_kpi_metric')->value('id'),
                'snapshot_date' => now()->toDateString(),
                'org_unit_id' => DB::table('ipa_org_unit')->value('id'),
                'country_id' => DB::table('ipa_country')->value('id'),
                'value_numeric' => 1.00,
                'value_text' => 'value_text_seed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
