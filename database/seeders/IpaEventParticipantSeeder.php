<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\Event;
use App\Models\EventParticipant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaEventParticipantSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_event_participant')->exists()) {
            return;
        }

        $eventIds = Event::orderBy('id')->pluck('id')->all();
        $userIds = AdminUser::orderBy('id')->pluck('id')->all();

        if ($eventIds === [] || $userIds === []) {
            return;
        }

        foreach ($eventIds as $index => $eventId) {
            $selectedUsers = collect($userIds)->shuffle()->take(3)->values();

            foreach ($selectedUsers as $participantIndex => $userId) {
                EventParticipant::factory()->create([
                    'event_id' => $eventId,
                    'user_id' => $userId,
                    'participation_status' => $participantIndex === 0 ? 1 : ($participantIndex === 1 ? 0 : 2),
                    'invited_at' => now()->subDays($index + $participantIndex),
                ]);
            }
        }
    }
}
