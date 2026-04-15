<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaMdNotificationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            ['code' => 'assignment', 'name_vi' => 'Phân công', 'default_channel' => 1],
            ['code' => 'approval', 'name_vi' => 'Phê duyệt', 'default_channel' => 1],
            ['code' => 'meeting', 'name_vi' => 'Lịch họp', 'default_channel' => 1],
            ['code' => 'system', 'name_vi' => 'Hệ thống', 'default_channel' => 1],
        ];

        foreach ($definitions as $definition) {
            if (DB::table('ipa_md_notification_type')->where('code', $definition['code'])->exists()) {
                continue;
            }

            DB::table('ipa_md_notification_type')->insert([
                'code' => $definition['code'],
                'name_vi' => $definition['name_vi'],
                'default_channel' => $definition['default_channel'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
