<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\OrgUnit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaRbacFakeDataSeeder extends Seeder
{
    public function run(): void
    {
        $roleCodes = [
            'ADMIN' => 'ADMIN',
            'DIRECTOR' => 'DIRECTOR',
            'MANAGER' => 'MANAGER',
            'STAFF' => 'STAFF',
        ];

        $roles = DB::table('ipa_role')
            ->whereIn('code', array_values($roleCodes))
            ->pluck('id', 'code')
            ->all();

        $unitIds = OrgUnit::orderBy('id')->pluck('id')->all();
        if (empty($unitIds)) {
            $this->command->error('No OrgUnits found. Please seed OrgUnits first.');
            return;
        }

        $usersToCreate = [
            ['type' => 'ADMIN', 'prefix' => 'admin', 'count' => 1, 'full_name_prefix' => 'Admin System'],
            ['type' => 'DIRECTOR', 'prefix' => 'director', 'count' => 1, 'full_name_prefix' => 'Director'],
            ['type' => 'MANAGER', 'prefix' => 'manager', 'count' => 2, 'full_name_prefix' => 'Manager'],
            ['type' => 'STAFF', 'prefix' => 'staff', 'count' => 50, 'full_name_prefix' => 'Staff'],
        ];

        DB::transaction(function () use ($usersToCreate, $roles, $unitIds, $roleCodes) {
            $managerUnits = [];

            foreach ($usersToCreate as $config) {
                $roleId = $roles[$roleCodes[$config['type']]] ?? null;

                if (!$roleId) {
                    $this->command->warn("Role code {$roleCodes[$config['type']]} not found. Skipping.");
                    continue;
                }

                for ($i = 1; $i <= $config['count']; $i++) {
                    $email = "{$config['prefix']}{$i}@gmail.com";
                    $username = "{$config['prefix']}{$i}";
                    
                    // Determine Unit ID based on hierarchy requirements
                    $unitId = $unitIds[0]; // Default to ROOT
                    
                    if ($config['type'] === 'MANAGER') {
                        // Manager 1 -> Unit 1, Manager 2 -> Unit 2
                        $unitId = $unitIds[$i] ?? $unitIds[0];
                        $managerUnits[$i] = $unitId;
                    } elseif ($config['type'] === 'STAFF') {
                        if ($i <= 10) {
                            $unitId = $unitIds[1] ?? $unitIds[0]; // Together with Manager 1
                        } elseif ($i <= 20) {
                            $unitId = $unitIds[2] ?? $unitIds[0]; // Together with Manager 2
                        } else {
                            // Others: round robin starting from unit 3
                            $unitId = $unitIds[($i % (count($unitIds) - 3)) + 3] ?? $unitIds[0];
                        }
                    }

                    $user = AdminUser::updateOrCreate(
                        ['email' => $email],
                        [
                            'username' => $username,
                            'full_name' => "{$config['full_name_prefix']} {$i}",
                            'phone' => '09' . str_pad((string)rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                            'status' => 1,
                            'password' => Hash::make('111111'),
                            'primary_unit_id' => $unitId,
                            'updated_at' => now(),
                        ]
                    );

                    // Assign role
                    DB::table('ipa_user_role')->updateOrInsert(
                        ['user_id' => $user->id, 'role_id' => $roleId],
                        [
                            'effective_from' => now(),
                            'effective_to' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );

                    // If user is a manager, set them as the manager of their unit
                    if ($config['type'] === 'MANAGER') {
                        OrgUnit::where('id', $unitId)->update(['manager_user_id' => $user->id]);
                    }
                }
            }
        });

        $this->command->info('Fake RBAC data seeded successfully.');
    }
}
