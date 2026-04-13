<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaMdEventTypeSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_md_event_type')->exists()) {
            return;
        }

        DB::table('ipa_md_event_type')->insert([
                'code' => 'IPA_MD_EVENT_TYPE_CODE',
                'name_vi' => 'name_vi_seed',
                'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
