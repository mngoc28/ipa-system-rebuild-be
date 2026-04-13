<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaTaskAssigneeSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_task_assignee')->exists()) {
            return;
        }

        DB::table('ipa_task_assignee')->insert([
                'task_id' => DB::table('ipa_task')->value('id'),
                'user_id' => DB::table('ipa_user')->value('id'),
                'assignment_type' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
