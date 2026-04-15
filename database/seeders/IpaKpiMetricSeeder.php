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
        $metrics = [
            [
                'metric_code' => 'CITY_NEW_PROJECTS_Q1_2026',
                'metric_name' => 'Số dự án mới quý I/2026',
                'unit' => 'projects',
                'scope_type' => 1,
            ],
            [
                'metric_code' => 'CITY_FDI_TOTAL_2026',
                'metric_name' => 'Tổng vốn FDI 2026',
                'unit' => 'vnd',
                'scope_type' => 1,
            ],
            [
                'metric_code' => 'CITY_DOMESTIC_CAPITAL_2026',
                'metric_name' => 'Vốn đăng ký nội địa 2026',
                'unit' => 'vnd',
                'scope_type' => 1,
            ],
            [
                'metric_code' => 'CITY_PCI_SATISFACTION_2026',
                'metric_name' => 'Chỉ số hài lòng PCI 2026',
                'unit' => 'score',
                'scope_type' => 1,
            ],
        ];

        foreach ($metrics as $metric) {
            if (DB::table('ipa_kpi_metric')->where('metric_code', $metric['metric_code'])->exists()) {
                continue;
            }

            DB::table('ipa_kpi_metric')->insert([
                ...$metric,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
