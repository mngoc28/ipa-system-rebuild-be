<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\Event;
use App\Models\Location;
use App\Models\Delegation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

final class IpaEventBulkSeeder extends Seeder
{
    public function run(): void
    {
        $delegationIds = Delegation::orderBy('id')->pluck('id')->all();
        $locationIds = Location::orderBy('id')->pluck('id')->all();
        $userIds = AdminUser::where('id', '>=', 41)->orderBy('id')->pluck('id')->all();

        if (empty($userIds) || empty($locationIds) || empty($delegationIds)) {
            return;
        }

        $eventTemplates = [
            'Họp triển khai dự án mới',
            'Khảo sát thực địa KCN Liên Chiểu',
            'Thẩm định hồ sơ năng lực nhà đầu tư',
            'Làm việc với Sở Kế hoạch và Đầu tư',
            'Tiếp đón đoàn đại biểu quốc tế',
            'Hội thảo xúc tiến đầu tư Nhật Bản',
            'Ký kết biên bản ghi nhớ hợp tác',
            'Rà soát tiến độ giải phóng mặt bằng',
            'Kiểm tra công tác bảo vệ môi trường',
            'Tư vấn chính sách ưu đãi đầu tư',
        ];

        $now = Carbon::now();

        for ($i = 0; $i < 100; $i++) {
            // Phân bổ thời gian ngẫu nhiên trong khoảng 30 ngày trước và sau hiện tại
            $daysOffset = rand(-30, 30);
            $hoursOffset = rand(8, 17);
            $startAt = (clone $now)->addDays($daysOffset)->setTime($hoursOffset, 0, 0);
            $endAt = (clone $startAt)->addHours(rand(1, 3));

            Event::factory()->create([
                'delegation_id' => $delegationIds[array_rand($delegationIds)],
                'title' => $eventTemplates[array_rand($eventTemplates)] . ' - #' . ($i + 1),
                'description' => 'Sự kiện bổ sung để làm phong phú dữ liệu lịch trình.',
                'event_type' => rand(1, 4),
                'status' => $startAt->isPast() ? 1 : 0,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'location_id' => $locationIds[array_rand($locationIds)],
                'organizer_user_id' => $userIds[array_rand($userIds)],
                'staff_id' => $userIds[array_rand($userIds)],
            ]);
        }
    }
}
