<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaSystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $updatedBy = DB::table('ipa_user')->value('id');

        $settings = [
            ['setting_key' => 'crm.partner.default_status', 'setting_group' => 'crm', 'setting_value' => '0', 'encrypted_value' => null, 'is_secret' => false],
            ['setting_key' => 'crm.partner.auto_promote_enabled', 'setting_group' => 'crm', 'setting_value' => '1', 'encrypted_value' => null, 'is_secret' => false],
            ['setting_key' => 'system.mail.from_name', 'setting_group' => 'mail', 'setting_value' => 'IPA Da Nang', 'encrypted_value' => null, 'is_secret' => false],
        ];

        foreach ($settings as $setting) {
            DB::table('ipa_system_setting')->updateOrInsert(
                ['setting_key' => $setting['setting_key']],
                [
                    'setting_group' => $setting['setting_group'],
                    'setting_value' => $setting['setting_value'],
                    'encrypted_value' => $setting['encrypted_value'],
                    'is_secret' => $setting['is_secret'],
                    'updated_by' => $updatedBy,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
