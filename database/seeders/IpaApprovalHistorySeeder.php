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
        $changedBy = (int) (DB::table('ipa_user')->min('id') ?? 1);

        foreach (DB::table('ipa_approval_request')->orderBy('id')->get() as $request) {
            $exists = DB::table('ipa_approval_history')->where('approval_request_id', $request->id)->exists();

            if ($exists) {
                continue;
            }

            DB::table('ipa_approval_history')->insert([
                'approval_request_id' => $request->id,
                'old_status' => 0,
                'new_status' => (int) $request->status,
                'changed_by' => $changedBy,
                'changed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
