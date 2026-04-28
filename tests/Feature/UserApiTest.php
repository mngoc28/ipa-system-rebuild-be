<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

final class UserApiTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $admin;
    private string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Admin role
        $adminRole = Role::factory()->create(['code' => 'ADMIN']);

        // Create Admin user
        $this->admin = AdminUser::factory()->create();
        $this->admin->roles()->attach($adminRole);
        $this->adminToken = JWTAuth::fromUser($this->admin);
    }

    /**
     * Test admin can list users.
     */
    public function test_admin_can_list_users(): void
    {
        AdminUser::factory()->count(5)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken
        ])->getJson('/api/v1/staff/users?pageSize=10');

        $response->assertStatus(200)
            ->assertJsonPath('api_status', 'success')
            ->assertJsonStructure([
                'items',
                'meta'
            ]);
        
        // Total users: 5 created + 1 admin from setUp = 6
        $this->assertCount(6, $response->json('items'));
    }

    /**
     * Test admin can create a user.
     */
    public function test_admin_can_create_user(): void
    {
        $userData = [
            'username' => 'testuser_new',
            'fullName' => 'Test User New',
            'email' => 'testuser_new@example.com',
            'password' => 'password123',
            'roleIds' => []
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken
        ])->postJson('/api/v1/staff/users', $userData);

        $response->assertStatus(201)
            ->assertJsonPath('api_status', 'success');

        $this->assertDatabaseHas('ipa_user', ['username' => 'testuser_new']);
    }

    /**
     * Test admin can update a user.
     */
    public function test_admin_can_update_user(): void
    {
        $user = AdminUser::factory()->create(['full_name' => 'Old Name']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken
        ])->patchJson("/api/v1/staff/users/{$user->id}", [
            'fullName' => 'New Name'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('ipa_user', [
            'id' => $user->id,
            'full_name' => 'New Name'
        ]);
    }

    /**
     * Test admin can delete a user.
     */
    public function test_admin_can_delete_user(): void
    {
        $user = AdminUser::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken
        ])->deleteJson("/api/v1/staff/users/{$user->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('ipa_user', ['id' => $user->id]);
    }

    /**
     * Test admin can lock/unlock a user.
     */
    public function test_admin_can_lock_user(): void
    {
        $user = AdminUser::factory()->create(['status' => 1]);

        // Lock
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken
        ])->patchJson("/api/v1/staff/users/{$user->id}/lock", [
            'locked' => true
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('ipa_user', [
            'id' => $user->id,
            'status' => 0
        ]);

        // Unlock
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken
        ])->patchJson("/api/v1/staff/users/{$user->id}/lock", [
            'locked' => false
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('ipa_user', [
            'id' => $user->id,
            'status' => 1
        ]);
    }

    /**
     * Test role-based access control: regular user cannot delete another user.
     */
    public function test_regular_user_cannot_delete_user(): void
    {
        $user1 = AdminUser::factory()->create();
        $user2 = AdminUser::factory()->create();
        $token = JWTAuth::fromUser($user1);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->deleteJson("/api/v1/staff/users/{$user2->id}");

        $response->assertStatus(403);
    }
}
