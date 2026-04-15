<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaNotificationChannelSeeder extends Seeder
{
    public function run(): void
    {
        $notificationIds = DB::table('ipa_notification')->orderBy('id')->pluck('id');

        foreach ($notificationIds as $notificationId) {
            $exists = DB::table('ipa_notification_channel')
                ->where('notification_id', $notificationId)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('ipa_notification_channel')->insert([
                'notification_id' => $notificationId,
                'channel_type' => 1,
                'provider_message_id' => 'provider_message_id_' . $notificationId,
                'sent_at' => now(),
                'fail_reason' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
