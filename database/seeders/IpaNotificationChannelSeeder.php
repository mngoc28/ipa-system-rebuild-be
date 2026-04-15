<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaNotificationChannelSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_notification_channel')->exists()) {
            return;
        }

        DB::table('ipa_notification_channel')->insert([
                'notification_id' => DB::table('ipa_notification')->value('id'),
                'channel_type' => 1,
                'provider_message_id' => 'provider_message_id_seed',
                'sent_at' => now(),
                'fail_reason' => 'fail_reason seed text',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
