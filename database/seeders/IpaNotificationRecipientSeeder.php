<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaNotificationRecipientSeeder extends Seeder
{
    public function run(): void
    {
        $notificationIds = DB::table('ipa_notification')->orderBy('id')->pluck('id');
        $userIds = DB::table('ipa_user')->orderBy('id')->pluck('id');

        if ($notificationIds->isEmpty() || $userIds->isEmpty()) {
            return;
        }

        foreach ($notificationIds as $index => $notificationId) {
            foreach ($userIds as $userId) {
                $exists = DB::table('ipa_notification_recipient')
                    ->where('notification_id', $notificationId)
                    ->where('recipient_user_id', $userId)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('ipa_notification_recipient')->insert([
                    'notification_id' => $notificationId,
                    'recipient_user_id' => $userId,
                    'delivery_status' => 1,
                    'read_at' => $index === 0 ? now() : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
