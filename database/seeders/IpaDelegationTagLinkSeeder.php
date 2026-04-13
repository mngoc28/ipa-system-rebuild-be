<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaDelegationTagLinkSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_delegation_tag_link')->exists()) {
            return;
        }

        DB::table('ipa_delegation_tag_link')->insert([
                'delegation_id' => DB::table('ipa_delegation')->value('id'),
                'tag_id' => DB::table('ipa_delegation_tag')->value('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
