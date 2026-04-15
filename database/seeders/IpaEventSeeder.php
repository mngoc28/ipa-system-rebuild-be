<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaEventSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_event')->exists()) {
            return;
        }

        DB::table('ipa_event')->insert([
                'delegation_id' => DB::table('ipa_delegation')->value('id'),
                'title' => 'title_seed',
                'description' => 'description seed text',
                'event_type' => 1,
                'status' => 1,
                'start_at' => now(),
                'end_at' => now(),
                'location_id' => DB::table('ipa_location')->value('id'),
                'organizer_user_id' => DB::table('ipa_user')->value('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
