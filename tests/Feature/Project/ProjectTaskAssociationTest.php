<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

test('task can be assigned to project upon creation and update', function () {
    $project = Project::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/tasks', [
            'title' => 'تسک مرتبط با پروژه',
            'project_id' => $project->id,
            'priority' => 'high',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('task.project_id', $project->id);

    $taskUuid = $response->json('task.uuid');

    // Update to detach from project
    $updateResponse = $this->actingAs($this->user, 'sanctum')
        ->putJson("/api/v1/tasks/{$taskUuid}", [
            'project_id' => null,
        ]);

    $updateResponse->assertStatus(200)
        ->assertJsonPath('task.project_id', null);
});

test('tasks can be filtered by project_id on tasks index endpoint', function () {
    $p1 = Project::factory()->create(['user_id' => $this->user->id]);
    $p2 = Project::factory()->create(['user_id' => $this->user->id]);

    $t1 = Task::factory()->create(['user_id' => $this->user->id, 'project_id' => $p1->id]);
    Task::factory()->create(['user_id' => $this->user->id, 'project_id' => $p2->id]);
    Task::factory()->create(['user_id' => $this->user->id, 'project_id' => null]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/tasks?project_id={$p1->id}");

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $t1->uuid);
});

test('project calculates accurate progress percentage and counts', function () {
    $project = Project::factory()->create(['user_id' => $this->user->id]);

    // 0 tasks -> 0%
    $res0 = $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/projects/{$project->uuid}");
    $res0->assertStatus(200)
        ->assertJsonPath('data.tasks_count', 0)
        ->assertJsonPath('data.completed_tasks_count', 0)
        ->assertJsonPath('data.pending_tasks_count', 0)
        ->assertJsonPath('data.progress_percentage', 0);

    // 1 completed, 1 pending -> 50%
    Task::factory()->completed()->create(['user_id' => $this->user->id, 'project_id' => $project->id]);
    Task::factory()->create(['user_id' => $this->user->id, 'project_id' => $project->id]);

    $res50 = $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/projects/{$project->uuid}");
    $res50->assertStatus(200)
        ->assertJsonPath('data.tasks_count', 2)
        ->assertJsonPath('data.completed_tasks_count', 1)
        ->assertJsonPath('data.pending_tasks_count', 1)
        ->assertJsonPath('data.progress_percentage', 50);

    // 2 completed, 0 pending -> 100%
    Task::where('project_id', $project->id)->update(['status' => 'completed', 'completed_at' => now()]);

    $res100 = $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/projects/{$project->uuid}");
    $res100->assertStatus(200)
        ->assertJsonPath('data.progress_percentage', 100);
});

test('deleting project without delete_tasks moves tasks to inbox by nullifying project_id', function () {
    $project = Project::factory()->create(['user_id' => $this->user->id]);
    $task = Task::factory()->create(['user_id' => $this->user->id, 'project_id' => $project->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->deleteJson("/api/v1/projects/{$project->uuid}?delete_tasks=0");

    $response->assertStatus(200);

    expect($task->fresh()->project_id)->toBeNull()
        ->and($task->fresh()->trashed())->toBeFalse();
});

test('deleting project with delete_tasks=1 deletes associated tasks', function () {
    $project = Project::factory()->create(['user_id' => $this->user->id]);
    $task = Task::factory()->create(['user_id' => $this->user->id, 'project_id' => $project->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->deleteJson("/api/v1/projects/{$project->uuid}?delete_tasks=1");

    $response->assertStatus(200);

    expect($task->fresh()->trashed())->toBeTrue();
});
