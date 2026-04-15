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
        $requesterUserId = (int) (DB::table('ipa_user')->min('id') ?? 1);

        $seeds = [
            [
                'status' => 0,
                'request_type' => 'MINUTES_APPROVAL',
                'ref_table' => 'ipa_minutes',
                'ref_id' => (int) (DB::table('ipa_minutes')->min('id') ?? 1),
                'current_step' => 1,
                'priority' => 2,
                'due_at' => now()->addDay(),
            ],
            [
                'status' => 1,
                'request_type' => 'DELEGATION_APPROVAL',
                'ref_table' => 'ipa_delegation',
                'ref_id' => (int) (DB::table('ipa_delegation')->min('id') ?? 1),
                'current_step' => 1,
                'priority' => 1,
                'due_at' => now()->subDay(),
            ],
            [
                'status' => 2,
                'request_type' => 'EVENT_APPROVAL',
                'ref_table' => 'ipa_event',
                'ref_id' => (int) (DB::table('ipa_event')->min('id') ?? 1),
                'current_step' => 1,
                'priority' => 1,
                'due_at' => now()->subDays(2),
            ],
        ];

        foreach ($seeds as $seed) {
            $exists = DB::table('ipa_approval_request')->where('status', $seed['status'])->exists();

            if ($exists) {
                continue;
            }

            DB::table('ipa_approval_request')->insert([
                'request_type' => $seed['request_type'],
                'ref_table' => $seed['ref_table'],
                'ref_id' => $seed['ref_id'],
                'requester_user_id' => $requesterUserId,
                'current_step' => $seed['current_step'],
                'priority' => $seed['priority'],
                'due_at' => $seed['due_at'],
                'status' => $seed['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
