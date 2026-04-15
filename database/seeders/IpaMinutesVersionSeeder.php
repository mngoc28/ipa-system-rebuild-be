<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaMinutesVersionSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_minutes_version')->exists()) {
            return;
        }

        DB::table('ipa_minutes_version')->insert([
                'minutes_id' => DB::table('ipa_minutes')->value('id'),
                'version_no' => 1,
                'content_text' => 'content_text seed text',
                'content_json' => json_encode(['seed' => true]),
                'change_summary' => 'change_summary seed text',
                'edited_by' => DB::table('ipa_user')->value('id'),
                'edited_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
