<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaMinutesCommentSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_minutes_comment')->exists()) {
            return;
        }

        DB::table('ipa_minutes_comment')->insert([
                'minutes_id' => DB::table('ipa_minutes')->value('id'),
                'version_id' => DB::table('ipa_minutes_version')->value('id'),
                'commenter_user_id' => DB::table('ipa_user')->value('id'),
                'parent_comment_id' => DB::table('ipa_minutes_comment')->value('id'),
                'comment_text' => 'comment_text seed text',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
