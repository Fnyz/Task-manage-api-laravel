<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_task(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/tasks', [
            'title' => 'Test Task',
            'description' => 'Testing task creation',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'user_id' => $user->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_tasks(): void
    {
        $response = $this->getJson('/api/tasks');

        $response->assertStatus(401);
    }

    public function test_user_can_only_see_their_own_tasks(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $user1->tasks()->create(['title' => 'User 1 Task']);
        $user2->tasks()->create(['title' => 'User 2 Task']);

        $response = $this->actingAs($user1, 'sanctum')->getJson('/api/tasks');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'tasks');
    }
}
