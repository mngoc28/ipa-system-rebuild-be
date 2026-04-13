<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaIntegrationHealthLogSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_integration_health_log')->exists()) {
            return;
        }

        DB::table('ipa_integration_health_log')->insert([
                'integration_id' => DB::table('ipa_integration_endpoint')->value('id'),
                'check_time' => now(),
                'status' => 1,
                'latency_ms' => 1,
                'message' => 'message seed text',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
