<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $types = DB::table('ipa_md_notification_type')->pluck('id', 'code');

        $definitions = [
            [
                'code' => 'assignment',
                'title' => 'Phân công nhiệm vụ mới',
                'body' => 'Bạn vừa được giao một nhiệm vụ mới cần xử lý.',
                'ref_table' => 'ipa_task',
                'ref_id' => 1,
                'severity' => 1,
            ],
            [
                'code' => 'approval',
                'title' => 'Yêu cầu phê duyệt đang chờ',
                'body' => 'Có một yêu cầu cần bạn xem xét và phê duyệt.',
                'ref_table' => 'ipa_approval_request',
                'ref_id' => 1,
                'severity' => 1,
            ],
            [
                'code' => 'meeting',
                'title' => 'Lịch họp đã được cập nhật',
                'body' => 'Lịch công tác đã thay đổi, vui lòng kiểm tra lại.',
                'ref_table' => 'ipa_event',
                'ref_id' => 1,
                'severity' => 1,
            ],
            [
                'code' => 'system',
                'title' => 'Thông báo hệ thống',
                'body' => 'Hệ thống đã đồng bộ dữ liệu thành công.',
                'ref_table' => 'ipa_system_setting',
                'ref_id' => null,
                'severity' => 0,
            ],
        ];

        foreach ($definitions as $definition) {
            $typeId = $types[$definition['code']] ?? null;

            if ($typeId === null) {
                continue;
            }

            if (DB::table('ipa_notification')->where('title', $definition['title'])->exists()) {
                continue;
            }

            DB::table('ipa_notification')->insert([
                'notification_type_id' => $typeId,
                'title' => $definition['title'],
                'body' => $definition['body'],
                'ref_table' => $definition['ref_table'],
                'ref_id' => $definition['ref_id'],
                'severity' => $definition['severity'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
