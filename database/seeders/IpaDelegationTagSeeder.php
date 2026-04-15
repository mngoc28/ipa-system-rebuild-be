<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaDelegationTagSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_delegation_tag')->exists()) {
            return;
        }

        DB::table('ipa_delegation_tag')->insert([
                'code' => 'IPA_DELEGATION_TAG_CODE',
                'name' => 'name_seed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
