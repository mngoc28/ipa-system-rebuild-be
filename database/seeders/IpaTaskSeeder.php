<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaTaskSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('ipa_user')->where('username', 'admin')->value('id') 
                   ?? DB::table('ipa_user')->where('username', 'admin_seed')->value('id') 
                   ?? 1;

        $tasks = [
            [
                'title' => 'Rà soát hồ sơ đối tác Samsung',
                'description' => 'Kiểm tra tính pháp lý của các giấy tờ liên quan.',
                'status' => 0, // Pending
                'priority' => 2, // High
                'due_at' => now()->addDays(3),
                'created_by' => $adminId,
            ],
            [
                'title' => 'Chuẩn bị nội dung buổi làm việc với Toyota',
                'description' => 'Soạn thảo biên bản ghi nhớ hợp tác mảng xe điện.',
                'status' => 1, // In Progress
                'priority' => 2, // High
                'due_at' => now()->addDays(5),
                'created_by' => $adminId,
            ],
            [
                'title' => 'Cập nhật danh sách khu công nghiệp Đà Nẵng',
                'description' => 'Bổ sung các diện tích còn trống trong năm 2026.',
                'status' => 2, // Completed
                'priority' => 1, // Normal
                'due_at' => now()->subDays(1),
                'created_by' => $adminId,
            ],
            [
                'title' => 'Gửi email phản hồi cho Intel',
                'description' => 'Trả lời về các câu hỏi chính sách ưu đãi đầu tư.',
                'status' => 0, // Pending
                'priority' => 3, // Urgent
                'due_at' => now()->addHours(4),
                'created_by' => $adminId,
            ],
            [
                'title' => 'Lên lịch họp hội đồng thành phố',
                'description' => 'Họp bàn về dự án khu trung tâm tài chính.',
                'status' => 0, // Pending
                'priority' => 2, // High
                'due_at' => now()->addDays(7),
                'created_by' => $adminId,
            ],
        ];

        foreach ($tasks as $task) {
            DB::table('ipa_task')->updateOrInsert(
                ['title' => $task['title']],
                array_merge($task, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
