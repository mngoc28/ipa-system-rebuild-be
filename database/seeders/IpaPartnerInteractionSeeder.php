<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaPartnerInteractionSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_partner_interaction')->exists()) {
            return;
        }

        DB::table('ipa_partner_interaction')->insert([
                'partner_id' => DB::table('ipa_partner')->value('id'),
                'interaction_type' => 1,
                'interaction_at' => now(),
                'owner_user_id' => DB::table('ipa_user')->value('id'),
                'summary' => 'summary seed text',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
