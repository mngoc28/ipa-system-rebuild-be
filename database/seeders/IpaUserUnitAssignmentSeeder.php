<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaUserUnitAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('ipa_user')->pluck('id')->values()->all();
        $units = DB::table('ipa_org_unit')->pluck('id')->values()->all();

        $assignments = [
            ['user_index' => 0, 'unit_index' => 0, 'position_title' => 'Giám đốc', 'is_primary' => true],
            ['user_index' => 1, 'unit_index' => 1, 'position_title' => 'Trưởng phòng', 'is_primary' => true],
            ['user_index' => 2, 'unit_index' => 2, 'position_title' => 'Chuyên viên', 'is_primary' => true],
        ];

        foreach ($assignments as $assignment) {
            $userId = $users[$assignment['user_index']] ?? null;
            $unitId = $units[$assignment['unit_index']] ?? null;

            if ($userId === null || $unitId === null) {
                continue;
            }

            DB::table('ipa_user_unit_assignment')->updateOrInsert(
                [
                    'user_id' => $userId,
                    'unit_id' => $unitId,
                ],
                [
                    'position_title' => $assignment['position_title'],
                    'is_primary' => $assignment['is_primary'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
