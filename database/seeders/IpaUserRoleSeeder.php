<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaUserRoleSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_user_role')->exists()) {
            return;
        }

        $userIds = DB::table('ipa_user')->orderBy('id')->pluck('id')->all();
        $roleIds = DB::table('ipa_role')->orderBy('id')->pluck('id')->all();

        if ($userIds === [] || $roleIds === []) {
            return;
        }

        $roleMap = [
            $roleIds[0],
            $roleIds[1] ?? $roleIds[0],
            $roleIds[2] ?? $roleIds[0],
            $roleIds[3] ?? ($roleIds[2] ?? $roleIds[0]),
        ];

        foreach ($userIds as $index => $userId) {
            DB::table('ipa_user_role')->insert([
                'user_id' => $userId,
                'role_id' => $roleMap[$index % count($roleMap)],
                'effective_from' => now()->subDays($index),
                'effective_to' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
