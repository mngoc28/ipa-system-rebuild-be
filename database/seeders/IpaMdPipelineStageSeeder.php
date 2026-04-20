<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaMdPipelineStageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['code' => 'LEAD', 'name_vi' => 'Tiềm năng', 'stage_order' => 1],
            ['code' => 'CONTACTED', 'name_vi' => 'Đã liên hệ', 'stage_order' => 2],
            ['code' => 'PROPOSAL', 'name_vi' => 'Đề xuất/Báo giá', 'stage_order' => 3],
            ['code' => 'NEGOTIATION', 'name_vi' => 'Thương thảo', 'stage_order' => 4],
            ['code' => 'CLOSED_WON', 'name_vi' => 'Thành công', 'stage_order' => 5],
            ['code' => 'CLOSED_LOST', 'name_vi' => 'Thất bại', 'stage_order' => 6],
        ];

        foreach ($stages as $stage) {
            DB::table('ipa_md_pipeline_stage')->updateOrInsert(
                ['code' => $stage['code']],
                array_merge($stage, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
