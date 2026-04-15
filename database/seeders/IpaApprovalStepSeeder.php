<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaApprovalStepSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_approval_step')->exists()) {
            return;
        }

        DB::table('ipa_approval_step')->insert([
                'approval_request_id' => DB::table('ipa_approval_request')->value('id'),
                'approver_user_id' => DB::table('ipa_user')->value('id'),
                'step_order' => 1,
                'decision' => 1,
                'decision_note' => 'decision_note seed text',
                'decided_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
