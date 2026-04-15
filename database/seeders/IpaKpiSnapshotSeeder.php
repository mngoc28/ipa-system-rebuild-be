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
        $orgUnitId = DB::table('ipa_org_unit')->value('id');
        $countryId = DB::table('ipa_country')->value('id');

        $snapshots = [
            'CITY_NEW_PROJECTS_Q1_2026' => 12.0,
            'CITY_FDI_TOTAL_2026' => 450000000.0,
            'CITY_DOMESTIC_CAPITAL_2026' => 2400000000000.0,
            'CITY_PCI_SATISFACTION_2026' => 4.9,
        ];

        foreach ($snapshots as $metricCode => $value) {
            $metricId = DB::table('ipa_kpi_metric')->where('metric_code', $metricCode)->value('id');

            if ($metricId === null) {
                continue;
            }

            if (DB::table('ipa_kpi_snapshot')->where('metric_id', $metricId)->whereDate('snapshot_date', now()->toDateString())->exists()) {
                continue;
            }

            DB::table('ipa_kpi_snapshot')->insert([
                'metric_id' => $metricId,
                'snapshot_date' => now()->toDateString(),
                'org_unit_id' => $orgUnitId,
                'country_id' => $countryId,
                'value_numeric' => $value,
                'value_text' => (string) $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
