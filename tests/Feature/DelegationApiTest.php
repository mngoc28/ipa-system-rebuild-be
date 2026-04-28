<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Country;
use App\Models\Delegation;
use App\Models\OrgUnit;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

final class DelegationApiTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $staffRole = Role::factory()->create(['code' => 'STAFF']);
        $this->user = AdminUser::factory()->create();
        $this->user->roles()->attach($staffRole);
        $this->token = JWTAuth::fromUser($this->user);
    }

    /**
     * Test listing delegations.
     */
    public function test_user_can_list_delegations(): void
    {
        Delegation::factory()->count(3)->create(['owner_user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/staff/delegations');

        $response->assertStatus(200)
            ->assertJsonPath('api_status', 'success');
    }

    /**
     * Test creating a delegation.
     */
    public function test_user_can_create_delegation(): void
    {
        $country = Country::factory()->create();
        $unit = OrgUnit::factory()->create();

        $data = [
            'name' => 'Japan Investment Delegation 2024',
            'direction' => 1,
            'country_id' => $country->id,
            'host_unit_id' => $unit->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'objective' => 'Trade promotion and high-tech investment',
            'members' => [
                [
                    'fullName' => 'Sato Kenji',
                    'role' => 'Lead Delegate',
                    'organizationName' => 'JETRO'
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/staff/delegations', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('ipa_delegation', ['name' => 'Japan Investment Delegation 2024']);
    }

    /**
     * Test updating a delegation.
     */
    public function test_user_can_update_delegation(): void
    {
        $delegation = Delegation::factory()->create([
            'name' => 'Old Delegation Name',
            'owner_user_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->patchJson("/api/v1/staff/delegations/{$delegation->id}", [
            'name' => 'Updated Delegation Name'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('ipa_delegation', [
            'id' => $delegation->id,
            'name' => 'Updated Delegation Name'
        ]);
    }

    /**
     * Test deleting a delegation.
     */
    public function test_user_can_delete_delegation(): void
    {
        $delegation = Delegation::factory()->create(['owner_user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/v1/staff/delegations/{$delegation->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('ipa_delegation', ['id' => $delegation->id]);
    }

    public function test_user_can_add_comment_to_delegation(): void
    {
        $delegation = Delegation::factory()->create(['owner_user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson("/api/v1/staff/delegations/{$delegation->id}/comments", [
            'content' => 'This is a test comment about the delegation.'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('ipa_delegation_comment', [
            'delegation_id' => $delegation->id,
            'comment_text' => 'This is a test comment about the delegation.'
        ]);
    }
}
