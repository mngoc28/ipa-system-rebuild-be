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
        $approverUserId = (int) (DB::table('ipa_user')->min('id') ?? 1);

        $requests = DB::table('ipa_approval_request')->orderBy('id')->get();

        foreach ($requests as $request) {
            $exists = DB::table('ipa_approval_step')->where('approval_request_id', $request->id)->exists();

            if ($exists) {
                continue;
            }

            $decision = match ((int) $request->status) {
                1 => 1,
                2 => 2,
                default => 0,
            };

            DB::table('ipa_approval_step')->insert([
                'approval_request_id' => $request->id,
                'approver_user_id' => $approverUserId,
                'step_order' => 1,
                'decision' => $decision,
                'decision_note' => $decision === 0 ? null : 'Seed decision note',
                'decided_at' => $decision === 0 ? null : now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
