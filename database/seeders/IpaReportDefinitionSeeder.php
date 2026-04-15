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
        $roleId = DB::table('ipa_role')->value('id');

        $definitions = [
            [
                'report_code' => 'CITY_INVESTMENT_MONTHLY',
                'report_name' => 'Báo cáo xúc tiến đầu tư thành phố theo tháng',
                'scope_type' => 1,
                'owner_role_id' => $roleId,
                'query_config' => json_encode(['scope' => 'city', 'period' => 'monthly', 'section' => 'investment']),
            ],
            [
                'report_code' => 'CITY_FDI_QUARTERLY',
                'report_name' => 'Báo cáo dòng vốn FDI quý',
                'scope_type' => 1,
                'owner_role_id' => $roleId,
                'query_config' => json_encode(['scope' => 'city', 'period' => 'quarterly', 'section' => 'fdi']),
            ],
            [
                'report_code' => 'CITY_PCI_FORECAST',
                'report_name' => 'Báo cáo dự báo PCI và tăng trưởng',
                'scope_type' => 1,
                'owner_role_id' => $roleId,
                'query_config' => json_encode(['scope' => 'city', 'period' => 'forecast', 'section' => 'pci']),
            ],
        ];

        foreach ($definitions as $definition) {
            if (DB::table('ipa_report_definition')->where('report_code', $definition['report_code'])->exists()) {
                continue;
            }

            DB::table('ipa_report_definition')->insert([
                ...$definition,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
