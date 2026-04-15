<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaMinutesCommentSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_minutes_comment')->exists()) {
            return;
        }

        DB::table('ipa_minutes_comment')->insert([
            [
                'minutes_id' => DB::table('ipa_minutes')->value('id'),
                'version_id' => DB::table('ipa_minutes_version')->value('id'),
                'commenter_user_id' => DB::table('ipa_user')->value('id'),
                'parent_comment_id' => null,
                'comment_text' => 'Nội dung biên bản đã rõ, chỉ cần bổ sung deadline cho từng đầu việc.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
