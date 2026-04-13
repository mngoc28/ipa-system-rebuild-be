<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaMessageTemplateSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_message_template')->exists()) {
            return;
        }

        DB::table('ipa_message_template')->insert([
                'template_code' => 'IPA_MESSAGE_TEMPLATE_CODE',
                'channel_type' => 1,
                'language_code' => 'IPA_MESSAGE_TEMPLATE_CODE',
                'subject_template' => 'subject_template seed text',
                'body_template' => 'body_template seed text',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
