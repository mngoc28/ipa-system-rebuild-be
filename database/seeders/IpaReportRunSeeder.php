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
        $runBy = DB::table('ipa_user')->value('id');

        $runs = [
            [
                'report_code' => 'CITY_INVESTMENT_MONTHLY',
                'output_file_name' => 'BaoCao_TongHop_QuyI_2026.pdf',
                'status' => 1,
                'started_at' => now()->subDays(1),
                'finished_at' => now()->subHours(20),
                'params_json' => json_encode(['scope' => 'city', 'period' => 'monthly']),
            ],
            [
                'report_code' => 'CITY_FDI_QUARTERLY',
                'output_file_name' => 'BaoCao_DongVon_FDI_2026.xlsx',
                'status' => 1,
                'started_at' => now()->subDays(2),
                'finished_at' => now()->subDays(1),
                'params_json' => json_encode(['scope' => 'city', 'period' => 'quarterly']),
            ],
            [
                'report_code' => 'CITY_PCI_FORECAST',
                'output_file_name' => 'BaoCao_DuBao_PCI_2026.pptx',
                'status' => 2,
                'started_at' => now()->subHours(5),
                'finished_at' => null,
                'params_json' => json_encode(['scope' => 'city', 'period' => 'forecast']),
            ],
        ];

        foreach ($runs as $run) {
            $definitionId = DB::table('ipa_report_definition')->where('report_code', $run['report_code'])->value('id');

            if ($definitionId === null) {
                continue;
            }

            $fileId = DB::table('ipa_file')->where('file_name', $run['output_file_name'])->value('id');

            if (DB::table('ipa_report_run')->where('report_definition_id', $definitionId)->where('started_at', $run['started_at'])->exists()) {
                continue;
            }

            DB::table('ipa_report_run')->insert([
                'report_definition_id' => $definitionId,
                'run_by' => $runBy,
                'params_json' => $run['params_json'],
                'output_file_id' => $fileId,
                'status' => $run['status'],
                'started_at' => $run['started_at'],
                'finished_at' => $run['finished_at'],
                'error_message' => $run['status'] === 3 ? 'Báo cáo bị lỗi khi dựng file.' : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
