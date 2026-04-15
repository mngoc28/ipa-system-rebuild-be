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
        if (DB::table('ipa_event_participant')->exists()) {
            return;
        }

        DB::table('ipa_event_participant')->insert([
                'event_id' => DB::table('ipa_event')->value('id'),
                'user_id' => DB::table('ipa_user')->value('id'),
                'participation_status' => 1,
                'invited_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
