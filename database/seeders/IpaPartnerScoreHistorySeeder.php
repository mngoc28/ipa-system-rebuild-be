<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaPartnerScoreHistorySeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_partner_score_history')->exists()) {
            return;
        }

        DB::table('ipa_partner_score_history')->insert([
                'partner_id' => DB::table('ipa_partner')->value('id'),
                'old_score' => 1.00,
                'new_score' => 1.00,
                'reason' => 'reason seed text',
                'changed_by' => DB::table('ipa_user')->value('id'),
                'changed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
