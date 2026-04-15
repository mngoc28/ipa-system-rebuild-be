<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaMinutesSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_minutes')->exists()) {
            return;
        }

        DB::table('ipa_minutes')->insert([
                'delegation_id' => DB::table('ipa_delegation')->value('id'),
                'event_id' => DB::table('ipa_event')->value('id'),
                'title' => 'title_seed',
                'current_version_no' => 1,
                'status' => 1,
                'owner_user_id' => DB::table('ipa_user')->value('id'),
                'approved_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
