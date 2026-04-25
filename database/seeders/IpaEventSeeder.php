<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\Event;
use App\Models\Location;
use App\Models\Delegation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

final class IpaEventSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_event')->exists()) {
            return;
        }

        $delegations = Delegation::orderBy('id')->get();
        $locationIds = Location::orderBy('id')->pluck('id')->all();
        $organizerIds = AdminUser::where('id', '>=', 41)->orderBy('id')->pluck('id')->all();
        if (empty($organizerIds)) {
            $organizerIds = AdminUser::orderBy('id')->pluck('id')->all();
        }

        // Dùng chung danh sách ID >= 41 cho cả organizer và staff_id để đảm bảo các tài khoản đăng nhập đều có lịch
        $staffIds = $organizerIds;

        if ($delegations->isEmpty() || $locationIds === [] || $organizerIds === []) {
            return;
        }

        $eventTemplates = [
            'Làm việc với nhà đầu tư',
            'Khảo sát cơ sở hạ tầng',
            'Tọa đàm chính sách ưu đãi',
            'Tham quan địa điểm đầu tư',
            'Gặp gỡ đối tác chiến lược',
        ];

        foreach ($delegations as $delegationIndex => $delegation) {
            for ($eventIndex = 0; $eventIndex < 10; $eventIndex++) {
                $startAt = Carbon::parse($delegation->start_date)
                    ->addHours(8 + ($eventIndex))
                    ->addDays($eventIndex % 3);
                $endAt = (clone $startAt)->addMinutes(60);

                Event::factory()->create([
                    'delegation_id' => $delegation->id,
                    'title' => $eventTemplates[($delegationIndex + $eventIndex) % count($eventTemplates)] . ' - ' . $delegation->code,
                    'description' => 'Phiên làm việc thuộc ' . $delegation->name . ' tại Đà Nẵng.',
                    'event_type' => (($eventIndex + $delegationIndex) % 4) + 1,
                    'status' => $startAt->isPast() ? 1 : 0,
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'location_id' => $locationIds[($delegationIndex + $eventIndex) % count($locationIds)],
                    'organizer_user_id' => $organizerIds[($delegationIndex * 10 + $eventIndex) % count($organizerIds)],
                    'staff_id' => $staffIds[($delegationIndex * 10 + $eventIndex) % count($staffIds)],
                ]);
            }
        }
    }
}
