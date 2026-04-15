<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaEventExternalParticipantSeeder extends Seeder
{
    public function run(): void
    {
        $eventId = DB::table('ipa_event')->value('id');

        if ($eventId === null) {
            return;
        }

        if (DB::table('ipa_event_external_participant')->where('event_id', $eventId)->exists()) {
            return;
        }

        DB::table('ipa_event_external_participant')->insert([
            'event_id' => $eventId,
            'full_name' => 'Nguyễn Quốc Huy',
            'organization_name' => 'Samsung Electronics Việt Nam',
            'email' => 'quochuy@samsung.example',
            'phone' => '0909000001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
