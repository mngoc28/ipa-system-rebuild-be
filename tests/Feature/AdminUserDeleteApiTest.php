<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\AdminUser;

final class AdminUserDeleteApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_delete_endpoint_removes_user_and_direct_assignments(): void
    {
        // 1. Setup Admin acting user
        $adminRoleId = DB::table('ipa_role')->insertGetId([
            'code' => 'ADMIN',
            'name' => 'System Administrator',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $adminUserId = DB::table('ipa_user')->insertGetId([
            'username' => 'test-admin',
            'email' => 'admin@test.com',
            'full_name' => 'Test Admin',
            'phone' => '0000000000',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ipa_user_role')->insert([
            'user_id' => $adminUserId,
            'role_id' => $adminRoleId,
            'effective_from' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $adminUser = AdminUser::find($adminUserId);
        $token = auth('api')->fromUser($adminUser);

        // 2. Setup User to be deleted
        $roleId = DB::table('ipa_role')->insertGetId([
            'code' => 'admin-user-delete-test-role',
            'name' => 'Admin User Delete Test Role',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $unitId = DB::table('ipa_org_unit')->insertGetId([
            'unit_code' => 'ADMIN-DELETE-TEST-UNIT',
            'unit_name' => 'Admin Delete Test Unit',
            'unit_type' => 'department',
            'parent_unit_id' => null,
            'manager_user_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId = DB::table('ipa_user')->insertGetId([
            'username' => 'admin-delete-user',
            'email' => 'admin-delete-user@danang.gov.vn',
            'full_name' => 'Admin Delete User',
            'phone' => '0900000000',
            'avatar_url' => null,
            'status' => 1,
            'primary_unit_id' => $unitId,
            'last_login_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ipa_user_role')->insert([
            'user_id' => $userId,
            'role_id' => $roleId,
            'effective_from' => now(),
            'effective_to' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ipa_user_unit_assignment')->insert([
            'user_id' => $userId,
            'unit_id' => $unitId,
            'position_title' => 'Operator',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/v1/admin/users/' . $userId);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('data.deleted', true);

        $this->assertDatabaseMissing('ipa_user', [
            'id' => $userId,
        ]);

        $this->assertDatabaseMissing('ipa_user_role', [
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);

        $this->assertDatabaseMissing('ipa_user_unit_assignment', [
            'user_id' => $userId,
            'unit_id' => $unitId,
        ]);
    }
}