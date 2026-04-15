<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaMdNotificationTypeSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_md_notification_type')->exists()) {
            return;
        }

        DB::table('ipa_md_notification_type')->insert([
                'code' => 'IPA_MD_NOTIFICATION_TYPE_CODE',
                'name_vi' => 'name_vi_seed',
                'default_channel' => 1,
                'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
