<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaOrgUnitSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_org_unit')->exists()) {
            return;
        }

        DB::table('ipa_org_unit')->insert([
                'unit_code' => 'IPA_ORG_UNIT_CODE',
                'unit_name' => 'unit_name_seed',
                'unit_type' => 'unit_type_seed',
                'parent_unit_id' => DB::table('ipa_org_unit')->value('id'),
                'manager_user_id' => DB::table('ipa_user')->value('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
