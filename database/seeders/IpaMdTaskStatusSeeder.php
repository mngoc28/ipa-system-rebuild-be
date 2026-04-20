<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaMdTaskStatusSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['code' => 'TODO', 'name_vi' => 'Cần làm', 'is_terminal' => false],
            ['code' => 'IN_PROGRESS', 'name_vi' => 'Đang thực hiện', 'is_terminal' => false],
            ['code' => 'DONE', 'name_vi' => 'Hoàn thành', 'is_terminal' => true],
        ];

        foreach ($rows as $row) {
            DB::table('ipa_md_task_status')->updateOrInsert(
                ['code' => $row['code']],
                [
                    'name_vi' => $row['name_vi'],
                    'is_terminal' => $row['is_terminal'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
