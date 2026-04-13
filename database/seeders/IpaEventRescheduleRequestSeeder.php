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
        if (DB::table('ipa_event_reschedule_request')->exists()) {
            return;
        }

        DB::table('ipa_event_reschedule_request')->insert([
                'event_id' => DB::table('ipa_event')->value('id'),
                'requested_by' => DB::table('ipa_user')->value('id'),
                'proposed_start_at' => now(),
                'proposed_end_at' => now(),
                'reason' => 'reason seed text',
                'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
