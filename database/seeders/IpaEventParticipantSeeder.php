<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaEventParticipantSeeder extends Seeder
{
    public function run(): void
    {
        $eventIds = DB::table('ipa_event')->orderBy('id')->pluck('id');
        $userIds = DB::table('ipa_user')->orderBy('id')->pluck('id');

        if ($eventIds->isEmpty() || $userIds->isEmpty()) {
            return;
        }

        foreach ($eventIds as $index => $eventId) {
            foreach ($userIds as $userId) {
                $exists = DB::table('ipa_event_participant')
                    ->where('event_id', $eventId)
                    ->where('user_id', $userId)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('ipa_event_participant')->insert([
                    'event_id' => $eventId,
                    'user_id' => $userId,
                    'participation_status' => $index === 0 ? 1 : 0,
                    'invited_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
