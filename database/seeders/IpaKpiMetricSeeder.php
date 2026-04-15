<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaKpiMetricSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_kpi_metric')->exists()) {
            return;
        }

        DB::table('ipa_kpi_metric')->insert([
                'metric_code' => 'IPA_KPI_METRIC_CODE',
                'metric_name' => 'metric_name_seed',
                'unit' => 'unit_seed',
                'scope_type' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
