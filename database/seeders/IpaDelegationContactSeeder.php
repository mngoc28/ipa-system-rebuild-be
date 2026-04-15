<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaDelegationContactSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_delegation_contact')->exists()) {
            return;
        }

        DB::table('ipa_delegation_contact')->insert([
                'delegation_id' => DB::table('ipa_delegation')->value('id'),
                'partner_contact_id' => DB::table('ipa_partner_contact')->value('id'),
                'name' => 'name_seed',
                'role_name' => 'role_name_seed',
                'email' => 'seed_ipa_delegation_contact@example.com',
                'phone' => '0900000000',
                'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
