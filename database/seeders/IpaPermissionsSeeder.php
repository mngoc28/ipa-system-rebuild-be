<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Delegation
            ['code' => 'delegation:view', 'module' => 'delegation', 'action' => 'view', 'name' => 'Xem danh sách đoàn'],
            ['code' => 'delegation:manage', 'module' => 'delegation', 'action' => 'manage', 'name' => 'Quản lý thông tin đoàn'],
            
            // Events
            ['code' => 'event:view', 'module' => 'event', 'action' => 'view', 'name' => 'Xem lịch sự kiện'],
            ['code' => 'event:manage', 'module' => 'event', 'action' => 'manage', 'name' => 'Quản lý sự kiện'],
            
            // Minutes
            ['code' => 'minutes:view', 'module' => 'minutes', 'action' => 'view', 'name' => 'Xem biên bản họp'],
            ['code' => 'minutes:manage', 'module' => 'minutes', 'action' => 'manage', 'name' => 'Quản lý biên bản'],
            ['code' => 'minutes:approve', 'module' => 'minutes', 'action' => 'approve', 'name' => 'Phê duyệt biên bản'],
            
            // Tasks
            ['code' => 'task:view', 'module' => 'task', 'action' => 'view', 'name' => 'Xem công việc'],
            ['code' => 'task:manage', 'module' => 'task', 'action' => 'manage', 'name' => 'Quản lý công việc'],
            
            // Partners
            ['code' => 'partner:view', 'module' => 'partner', 'action' => 'view', 'name' => 'Xem đối tác'],
            ['code' => 'partner:manage', 'module' => 'partner', 'action' => 'manage', 'name' => 'Quản lý đối tác'],
            
            // Users & Admin
            ['code' => 'user:view', 'module' => 'user', 'action' => 'view', 'name' => 'Xem người dùng'],
            ['code' => 'user:manage', 'module' => 'user', 'action' => 'manage', 'name' => 'Quản lý người dùng'],
            ['code' => 'system:settings', 'module' => 'system', 'action' => 'settings', 'name' => 'Cài đặt hệ thống'],
            ['code' => 'system:audit', 'module' => 'system', 'action' => 'audit', 'name' => 'Xem nhật ký hệ thống'],
        ];

        foreach ($permissions as $perm) {
            DB::table('ipa_permission')->updateOrInsert(
                ['code' => $perm['code']],
                [
                    'module' => $perm['module'],
                    'action' => $perm['action'],
                    'name' => $perm['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $allPerms = DB::table('ipa_permission')->pluck('id', 'code')->all();

        $roleMappings = [
            'ADMIN' => array_keys($allPerms),
            'DIRECTOR' => [
                'delegation:view', 'delegation:manage',
                'event:view', 'event:manage',
                'minutes:view', 'minutes:manage', 'minutes:approve',
                'task:view', 'task:manage',
                'partner:view', 'partner:manage',
                'user:view', 'system:audit'
            ],
            'MANAGER' => [
                'delegation:view', 'delegation:manage',
                'event:view', 'event:manage',
                'minutes:view', 'minutes:manage',
                'task:view', 'task:manage',
                'partner:view'
            ],
            'STAFF' => [
                'delegation:view',
                'event:view',
                'minutes:view',
                'task:view', 'task:manage'
            ],
        ];

        foreach ($roleMappings as $roleCode => $permCodes) {
            $roleId = DB::table('ipa_role')->where('code', $roleCode)->value('id');
            if (!$roleId) continue;

            foreach ($permCodes as $pCode) {
                $pId = $allPerms[$pCode] ?? null;
                if (!$pId) continue;

                DB::table('ipa_role_permission')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_id' => $pId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }
}
