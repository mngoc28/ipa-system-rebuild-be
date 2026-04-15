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
        $types = [
            ['code' => 'MEETING', 'name_vi' => 'Họp'],
            ['code' => 'VISIT', 'name_vi' => 'Thăm hỏi'],
            ['code' => 'WORKSHOP', 'name_vi' => 'Hội thảo'],
            ['code' => 'CEREMONY', 'name_vi' => 'Lễ nghi'],
        ];

        foreach ($types as $type) {
            if (DB::table('ipa_md_event_type')->where('code', $type['code'])->exists()) {
                continue;
            }

            DB::table('ipa_md_event_type')->insert([
                'code' => $type['code'],
                'name_vi' => $type['name_vi'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
