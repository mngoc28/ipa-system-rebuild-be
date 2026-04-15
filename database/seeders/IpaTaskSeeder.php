<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaTaskSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_task')->exists()) {
            return;
        }

        DB::table('ipa_task')->insert([
                'delegation_id' => DB::table('ipa_delegation')->value('id'),
                'event_id' => DB::table('ipa_event')->value('id'),
                'minutes_id' => DB::table('ipa_minutes')->value('id'),
                'title' => 'title_seed',
                'description' => 'description seed text',
                'status' => 1,
                'priority' => 1,
                'due_at' => now(),
                'is_overdue_cache' => true,
                'created_by' => DB::table('ipa_user')->value('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
