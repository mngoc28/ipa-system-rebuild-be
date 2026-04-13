<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaIntegrationEndpointSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_integration_endpoint')->exists()) {
            return;
        }

        DB::table('ipa_integration_endpoint')->insert([
                'provider_code' => 'IPA_INTEGRATION_ENDPOINT_CODE',
                'base_url' => 'base_url seed text',
                'app_id' => 'app_id_seed',
                'secret_ref' => 'secret_ref_seed',
                'status' => 1,
                'last_check_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
