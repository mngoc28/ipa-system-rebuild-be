<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaNotificationSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_notification')->exists()) {
            return;
        }

        DB::table('ipa_notification')->insert([
                'notification_type_id' => DB::table('ipa_md_notification_type')->value('id'),
                'title' => 'title_seed',
                'body' => 'body seed text',
                'ref_table' => 'ref_table_seed',
                'ref_id' => null,
                'severity' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
