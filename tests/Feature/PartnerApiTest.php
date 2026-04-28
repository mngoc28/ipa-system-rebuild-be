<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Country;
use App\Models\Partner;
use App\Models\Role;
use App\Models\Sector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

final class PartnerApiTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        // Create STAFF role
        $staffRole = Role::factory()->create(['code' => 'STAFF']);

        $this->user = AdminUser::factory()->create();
        $this->user->roles()->attach($staffRole);
        $this->token = JWTAuth::fromUser($this->user);
    }

    /**
     * Test listing partners.
     */
    public function test_user_can_list_partners(): void
    {
        Partner::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/staff/partners');

        $response->assertStatus(200)
            ->assertJsonPath('api_status', 'success')
            ->assertJsonStructure(['items', 'meta']);
    }

    /**
     * Test creating a partner.
     */
    public function test_user_can_create_partner(): void
    {
        $country = Country::factory()->create();
        $sector = Sector::factory()->create();

        $partnerData = [
            'partner_code' => 'P-TEST-001',
            'partner_name' => 'Global Investment Corp',
            'country_id' => $country->id,
            'sector_id' => $sector->id,
            'status' => 1,
            'website' => 'https://example.com'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/staff/partners', $partnerData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('ipa_partner', ['partner_code' => 'P-TEST-001']);
    }

    /**
     * Test updating a partner.
     */
    public function test_user_can_update_partner(): void
    {
        $partner = Partner::factory()->create(['partner_name' => 'Old Name']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->patchJson("/api/v1/staff/partners/{$partner->id}", [
            'partner_name' => 'New Name'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('ipa_partner', [
            'id' => $partner->id,
            'partner_name' => 'New Name'
        ]);
    }

    /**
     * Test deleting a partner.
     */
    public function test_user_can_delete_partner(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/v1/staff/partners/{$partner->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('ipa_partner', ['id' => $partner->id]);
    }

    /**
     * Test adding a contact to a partner.
     */
    public function test_user_can_add_contact_to_partner(): void
    {
        $partner = Partner::factory()->create();

        $contactData = [
            'fullName' => 'John Doe',
            'title' => 'CEO',
            'email' => 'john@example.com',
            'phone' => '123456789',
            'isPrimary' => true
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson("/api/v1/staff/partners/{$partner->id}/contacts", $contactData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('ipa_partner_contact', [
            'partner_id' => $partner->id,
            'full_name' => 'John Doe'
        ]);
    }

    /**
     * Test recording an interaction with a partner.
     */
    public function test_user_can_record_interaction(): void
    {
        $partner = Partner::factory()->create();

        $interactionData = [
            'interactionType' => 1,
            'interactionAt' => now()->toDateTimeString(),
            'summary' => 'Initial meeting'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson("/api/v1/staff/partners/{$partner->id}/interactions", $interactionData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('ipa_partner_interaction', [
            'partner_id' => $partner->id,
            'summary' => 'Initial meeting'
        ]);
    }
}
