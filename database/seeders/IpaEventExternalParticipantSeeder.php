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
        if (DB::table('ipa_event_external_participant')->exists()) {
            return;
        }

        DB::table('ipa_event_external_participant')->insert([
                'event_id' => DB::table('ipa_event')->value('id'),
                'full_name' => 'full_name_seed',
                'organization_name' => 'organization_name_seed',
                'email' => 'seed_ipa_event_external_participant@example.com',
                'phone' => '0900000000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
