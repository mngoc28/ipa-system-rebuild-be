<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\Delegation;
use App\Models\Event;
use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

final class IpaTaskSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_task')->exists()) {
            return;
        }

        $delegations = Delegation::orderBy('id')->get();
        $eventMap = Event::orderBy('id')->get()->groupBy('delegation_id');
        $userIds = AdminUser::orderBy('id')->pluck('id')->all();

        if ($delegations->isEmpty() || $userIds === []) {
            return;
        }

        $taskTemplates = [
            'Rà soát hồ sơ nhà đầu tư',
            'Soạn thư mời làm việc',
            'Cập nhật tiến độ sau cuộc họp',
            'Bổ sung tài liệu thuyết minh',
            'Gửi email xác nhận lịch hẹn',
            'Chuẩn bị nội dung trình bày',
            'Theo dõi phản hồi đối tác',
            'Lập biên bản họp',
            'Cập nhật dashboard tiến độ',
        ];

        $overdueTarget = 11;
        $taskIndex = 0;

        foreach ($delegations as $delegationIndex => $delegation) {
            for ($index = 0; $index < 9; $index++) {
                $isOverdue = $taskIndex < $overdueTarget;
                $dueAt = $isOverdue
                    ? Carbon::now()->subDays(1 + $taskIndex)
                    : Carbon::now()->addDays(2 + $index + $delegationIndex);

                $status = $isOverdue ? ($taskIndex % 2 === 0 ? 0 : 1) : ($index % 3);
                $eventIds = $eventMap[$delegation->id] ?? collect();
                $eventId = $eventIds->isNotEmpty() && $index % 2 === 0
                    ? $eventIds->values()[$index % $eventIds->count()]->id
                    : null;

                Task::factory()->create([
                    'delegation_id' => $delegation->id,
                    'event_id' => $eventId,
                    'title' => $taskTemplates[($delegationIndex + $index) % count($taskTemplates)] . ' - ' . $delegation->code,
                    'description' => 'Công việc phục vụ ' . $delegation->name . '.',
                    'status' => $status,
                    'priority' => ($index % 3) + 1,
                    'due_at' => $dueAt,
                    'is_overdue_cache' => $isOverdue,
                    'created_by' => $userIds[($delegationIndex + $index) % count($userIds)],
                ]);

                $taskIndex++;
            }
        }
    }
}
