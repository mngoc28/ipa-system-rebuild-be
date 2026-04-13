<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaApprovalRequestSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_approval_request')->exists()) {
            return;
        }

        DB::table('ipa_approval_request')->insert([
                'request_type' => 'request_type_seed',
                'ref_table' => 'ref_table_seed',
                'ref_id' => 1,
                'requester_user_id' => DB::table('ipa_user')->value('id'),
                'current_step' => 1,
                'priority' => 1,
                'due_at' => now(),
                'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
