<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaNotificationRecipientSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_notification_recipient')->exists()) {
            return;
        }

        DB::table('ipa_notification_recipient')->insert([
                'notification_id' => DB::table('ipa_notification')->value('id'),
                'recipient_user_id' => DB::table('ipa_user')->value('id'),
                'delivery_status' => 1,
                'read_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
