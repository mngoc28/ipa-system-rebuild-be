<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaUserUnitAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_user_unit_assignment')->exists()) {
            return;
        }

        DB::table('ipa_user_unit_assignment')->insert([
                'user_id' => DB::table('ipa_user')->value('id'),
                'unit_id' => DB::table('ipa_org_unit')->value('id'),
                'position_title' => 'position_title_seed',
                'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
