<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaTaskCommentSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_task_comment')->exists()) {
            return;
        }

        $taskRows = Task::orderBy('id')->get();
        $userIds = AdminUser::orderBy('id')->pluck('id')->all();

        if ($taskRows->isEmpty() || $userIds === []) {
            return;
        }

        foreach ($taskRows as $index => $task) {
            for ($commentIndex = 0; $commentIndex < 2; $commentIndex++) {
                TaskComment::factory()->create([
                    'task_id' => $task->id,
                    'commenter_user_id' => $userIds[($index + $commentIndex) % count($userIds)],
                    'comment_text' => ($commentIndex === 0 ? 'Đã cập nhật tiến độ cho ' : 'Tiếp tục theo dõi ') . $task->title . '.',
                ]);
            }
        }
    }
}
