<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaMinutesVersionSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_minutes_version')->exists()) {
            return;
        }

        $minutesIds = DB::table('ipa_minutes')->pluck('id')->all();

        DB::table('ipa_minutes_version')->insert([
            [
                'minutes_id' => $minutesIds[0] ?? DB::table('ipa_minutes')->value('id'),
                'version_no' => 1,
                'content_text' => 'Thành phần tham dự đã được xác nhận và lịch làm việc được chốt.',
                'content_json' => json_encode(['sections' => ['attendance', 'agenda', 'actions']]),
                'change_summary' => 'Khởi tạo bản nháp đầu tiên',
                'edited_by' => DB::table('ipa_user')->value('id'),
                'edited_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'minutes_id' => $minutesIds[1] ?? ($minutesIds[0] ?? DB::table('ipa_minutes')->value('id')),
                'version_no' => 1,
                'content_text' => 'Biên bản đang chờ duyệt nội bộ.',
                'content_json' => json_encode(['status' => 'pending']),
                'change_summary' => 'Tạo bản dự thảo chờ duyệt',
                'edited_by' => DB::table('ipa_user')->value('id'),
                'edited_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
