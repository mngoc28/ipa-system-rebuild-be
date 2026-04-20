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
        $organizerIds = AdminUser::orderBy('id')->pluck('id')->all();

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
            for ($eventIndex = 0; $eventIndex < 3; $eventIndex++) {
                $startAt = Carbon::parse($delegation->start_date)
                    ->addHours(9 + ($eventIndex * 2))
                    ->addDays($eventIndex);
                $endAt = (clone $startAt)->addMinutes(90);

                Event::factory()->create([
                    'delegation_id' => $delegation->id,
                    'title' => $eventTemplates[($delegationIndex + $eventIndex) % count($eventTemplates)] . ' - ' . $delegation->code,
                    'description' => 'Phiên làm việc thuộc ' . $delegation->name . ' tại Đà Nẵng.',
                    'event_type' => (($eventIndex + $delegationIndex) % 4) + 1,
                    'status' => $startAt->isPast() ? 1 : 0,
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'location_id' => $locationIds[($delegationIndex + $eventIndex) % count($locationIds)],
                    'organizer_user_id' => $organizerIds[($delegationIndex + $eventIndex) % count($organizerIds)],
                ]);
            }
        }
    }
}
