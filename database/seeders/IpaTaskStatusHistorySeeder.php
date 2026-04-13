<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaTaskStatusHistorySeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_task_status_history')->exists()) {
            return;
        }

        DB::table('ipa_task_status_history')->insert([
                'task_id' => DB::table('ipa_task')->value('id'),
                'old_status' => 1,
                'new_status' => 1,
                'changed_by' => DB::table('ipa_user')->value('id'),
                'changed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
