<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaMdWorkflowStatusSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['domain_code' => 'WORKFLOW', 'code' => 'DRAFT', 'name_vi' => 'Bản nháp', 'sort_order' => 1],
            ['domain_code' => 'WORKFLOW', 'code' => 'IN_PROGRESS', 'name_vi' => 'Đang xử lý', 'sort_order' => 2],
            ['domain_code' => 'WORKFLOW', 'code' => 'COMPLETED', 'name_vi' => 'Hoàn thành', 'sort_order' => 3],
        ];

        foreach ($rows as $row) {
            DB::table('ipa_md_workflow_status')->updateOrInsert(
                ['domain_code' => $row['domain_code'], 'code' => $row['code']],
                [
                    'name_vi' => $row['name_vi'],
                    'sort_order' => $row['sort_order'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
