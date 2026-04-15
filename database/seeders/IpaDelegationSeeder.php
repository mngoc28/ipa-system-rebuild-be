<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaDelegationSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_delegation')->exists()) {
            return;
        }

        DB::table('ipa_delegation')->insert([
                'code' => 'IPA_DELEGATION_CODE',
                'name' => 'name_seed',
                'direction' => 1,
                'status' => 1,
                'priority' => 1,
                'country_id' => DB::table('ipa_country')->value('id'),
                'host_unit_id' => DB::table('ipa_org_unit')->value('id'),
                'owner_user_id' => DB::table('ipa_user')->value('id'),
                'start_date' => now()->toDateString(),
                'end_date' => now()->toDateString(),
                'participant_count' => 1,
                'objective' => 'objective seed text',
                'description' => 'description seed text',
                'deleted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
