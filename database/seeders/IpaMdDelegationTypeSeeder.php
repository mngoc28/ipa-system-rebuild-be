<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaMdDelegationTypeSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_md_delegation_type')->exists()) {
            return;
        }

        DB::table('ipa_md_delegation_type')->insert([
                'code' => 'IPA_MD_DELEGATION_TYPE_CODE',
                'name_vi' => 'name_vi_seed',
                'is_active' => true,
                'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
