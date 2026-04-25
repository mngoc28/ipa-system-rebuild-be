<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Delegation;
use App\Models\Event;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

final class EventApiTest extends TestCase
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
     * Test listing events.
     */
    public function test_user_can_list_events(): void
    {
        Event::factory()->count(3)->create([
            'organizer_user_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/staff/events');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(3, 'items');
    }

    /**
     * Test creating an event.
     */
    public function test_user_can_create_event(): void
    {
        $delegation = Delegation::factory()->create(['owner_user_id' => $this->user->id]);

        $data = [
            'delegationId' => (string) $delegation->id,
            'title' => 'Important Meeting',
            'description' => 'Discussing investment opportunities',
            'eventType' => 'MEETING',
            'status' => 'PLANNED',
            'startAt' => now()->addDay()->toIso8601String(),
            'endAt' => now()->addDay()->addHours(2)->toIso8601String(),
            'organizerUserId' => (string) $this->user->id,
            'participantUserIds' => []
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/staff/events', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('ipa_event', ['title' => 'Important Meeting']);
    }

    /**
     * Test showing an event.
     */
    public function test_user_can_show_event(): void
    {
        $event = Event::factory()->create([
            'organizer_user_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/v1/staff/events/{$event->id}");

        $response->assertStatus(200)
            ->assertJsonPath('event.event.id', (string) $event->id);
    }

    /**
     * Test updating an event.
     */
    public function test_user_can_update_event(): void
    {
        $event = Event::factory()->create([
            'organizer_user_id' => $this->user->id,
            'title' => 'Old Title'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->patchJson("/api/v1/staff/events/{$event->id}", [
            'title' => 'Updated Title'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('ipa_event', [
            'id' => $event->id,
            'title' => 'Updated Title'
        ]);
    }

    /**
     * Test deleting an event.
     */
    public function test_user_can_delete_event(): void
    {
        $event = Event::factory()->create([
            'organizer_user_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/v1/staff/events/{$event->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('ipa_event', ['id' => $event->id]);
    }

    /**
     * Test joining an event.
     */
    public function test_user_can_join_event(): void
    {
        $event = Event::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson("/api/v1/staff/events/{$event->id}/join", [
            'joined' => true
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('ipa_event_participant', [
            'event_id' => $event->id,
            'user_id' => $this->user->id,
            'participation_status' => 1 // JOINED
        ]);
    }

    /**
     * Test requesting reschedule.
     */
    public function test_user_can_request_reschedule(): void
    {
        $event = Event::factory()->create([
            'organizer_user_id' => $this->user->id
        ]);

        $data = [
            'proposedStartAt' => now()->addDays(2)->toIso8601String(),
            'proposedEndAt' => now()->addDays(2)->addHours(1)->toIso8601String(),
            'reason' => 'Previous appointment conflict'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson("/api/v1/staff/events/{$event->id}/reschedule-requests", $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('ipa_event_reschedule_request', [
            'event_id' => $event->id,
            'reason' => 'Previous appointment conflict'
        ]);
    }
}
