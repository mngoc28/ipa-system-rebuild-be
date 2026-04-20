<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaDelegationOutcomeSeeder extends Seeder
{
    public function run(): void
    {
        $delegationIds = DB::table('ipa_delegation')->pluck('id')->values()->all();

        $outcomes = [
            ['index' => 0, 'progress_percent' => 85, 'summary' => 'Đã hoàn tất làm việc với đối tác Samsung và chốt danh sách đầu việc tiếp theo.', 'next_steps' => 'Theo dõi phản hồi về mặt bằng và nhu cầu nhân lực.'],
            ['index' => 1, 'progress_percent' => 70, 'summary' => 'Đã thống nhất lộ trình xúc tiến với nhóm fintech Singapore.', 'next_steps' => 'Bổ sung tài liệu ưu đãi và kế hoạch tiếp cận nhà đầu tư.'],
            ['index' => 2, 'progress_percent' => 60, 'summary' => 'Đang hoàn thiện ghi nhận hợp tác với đối tác Nhật Bản.', 'next_steps' => 'Lập lịch họp kỹ thuật và tổng hợp yêu cầu hạ tầng.'],
        ];

        foreach ($outcomes as $outcome) {
            $delegationId = $delegationIds[$outcome['index']] ?? null;

            if ($delegationId === null) {
                continue;
            }

            DB::table('ipa_delegation_outcome')->updateOrInsert(
                ['delegation_id' => $delegationId],
                [
                    'progress_percent' => $outcome['progress_percent'],
                    'summary' => $outcome['summary'],
                    'next_steps' => $outcome['next_steps'],
                    'report_updated_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
