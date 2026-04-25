<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Delegation;
use App\Models\Role;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

final class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $staffRole = Role::factory()->create(['code' => 'STAFF']);
        $this->user = AdminUser::factory()->create(['full_name' => 'Test Staff']);
        $this->user->roles()->attach($staffRole);
        $this->token = JWTAuth::fromUser($this->user);
    }

    /**
     * Test listing tasks.
     */
    public function test_user_can_list_tasks(): void
    {
        Task::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/staff/tasks');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
        
        // Check if items are in root (flattened) or in data
        $response->assertJsonCount(3, 'items');
    }

    /**
     * Test creating a task.
     */
    public function test_user_can_create_task(): void
    {
        $delegation = Delegation::factory()->create();

        $data = [
            'title' => 'Review Investment Proposal',
            'description' => 'Review the proposal from Samsung',
            'status' => 0,
            'priority' => 2,
            'delegation_id' => $delegation->id,
            'assignee_ids' => [$this->user->id]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/staff/tasks', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('ipa_task', ['title' => 'Review Investment Proposal']);
    }

    /**
     * Test adding a comment to a task.
     */
    public function test_user_can_add_comment_to_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson("/api/v1/staff/tasks/{$task->id}/comments", [
            'content' => 'I am working on this task.'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('ipa_task_comment', [
            'task_id' => $task->id,
            'comment_text' => 'I am working on this task.'
        ]);
    }

    /**
     * Test uploading an attachment to a task.
     */
    public function test_user_can_upload_attachment_to_task(): void
    {
        Storage::fake('public');
        $task = Task::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson("/api/v1/staff/tasks/{$task->id}/attachments", [
            'file' => $file
        ]);

        $response->assertStatus(200);
        if ($response->status() !== 200) {
            dd($response->json());
        }
        $this->assertDatabaseHas('ipa_task_attachment', [
            'task_id' => $task->id,
            'file_name' => 'document.pdf'
        ]);
    }
}
