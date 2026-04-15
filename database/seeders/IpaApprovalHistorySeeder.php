<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaApprovalHistorySeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_approval_history')->exists()) {
            return;
        }

        DB::table('ipa_approval_history')->insert([
                'approval_request_id' => DB::table('ipa_approval_request')->value('id'),
                'old_status' => 1,
                'new_status' => 1,
                'changed_by' => DB::table('ipa_user')->value('id'),
                'changed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
