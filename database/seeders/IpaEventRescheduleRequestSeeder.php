<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaEventRescheduleRequestSeeder extends Seeder
{
    public function run(): void
    {
        $eventId = DB::table('ipa_event')->value('id');
        $userId = DB::table('ipa_user')->value('id');

        if ($eventId === null || $userId === null) {
            return;
        }

        if (DB::table('ipa_event_reschedule_request')->where('event_id', $eventId)->exists()) {
            return;
        }

        DB::table('ipa_event_reschedule_request')->insert([
            'event_id' => $eventId,
            'requested_by' => $userId,
            'proposed_start_at' => now()->addDay()->setTime(14, 0),
            'proposed_end_at' => now()->addDay()->setTime(15, 30),
            'reason' => 'Đề xuất dời lịch do thay đổi lịch công tác',
            'status' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
