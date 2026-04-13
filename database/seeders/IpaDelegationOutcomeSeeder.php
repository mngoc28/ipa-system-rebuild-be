<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaDelegationOutcomeSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_delegation_outcome')->exists()) {
            return;
        }

        DB::table('ipa_delegation_outcome')->insert([
                'delegation_id' => DB::table('ipa_delegation')->value('id'),
                'progress_percent' => 1.00,
                'summary' => 'summary seed text',
                'next_steps' => 'next_steps seed text',
                'report_updated_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
