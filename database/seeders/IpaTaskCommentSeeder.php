<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaTaskCommentSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_task_comment')->exists()) {
            return;
        }

        DB::table('ipa_task_comment')->insert([
                'task_id' => DB::table('ipa_task')->value('id'),
                'commenter_user_id' => DB::table('ipa_user')->value('id'),
                'comment_text' => 'comment_text seed text',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
