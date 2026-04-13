<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaDelegationMemberSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_delegation_member')->exists()) {
            return;
        }

        DB::table('ipa_delegation_member')->insert([
                'delegation_id' => DB::table('ipa_delegation')->value('id'),
                'full_name' => 'full_name_seed',
                'title' => 'title_seed',
                'organization_name' => 'organization_name_seed',
                'contact_email' => 'seed_ipa_delegation_member@example.com',
                'contact_phone' => '0900000000',
                'member_type' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
