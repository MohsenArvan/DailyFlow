<?php

use App\Enums\TaskStatus;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['timezone' => 'Asia/Tehran']);
    $this->userToday = now('Asia/Tehran')->format('Y-m-d');
});

test('completing a task marks it completed and cascades to subtasks', function () {
    $task = Task::factory()->create([
        'user_id' => $this->user->id,
        'status' => TaskStatus::PENDING,
    ]);

    $subtask1 = Subtask::factory()->create([
        'task_id' => $task->id,
        'user_id' => $this->user->id,
        'is_completed' => false,
    ]);

    $subtask2 = Subtask::factory()->create([
        'task_id' => $task->id,
        'user_id' => $this->user->id,
        'is_completed' => true,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/tasks/{$task->uuid}/complete");

    $response->assertStatus(200)
        ->assertJsonPath('task.status', 'completed')
        ->assertJsonPath('task.subtasks_progress', 100);

    expect($task->fresh()->status)->toBe(TaskStatus::COMPLETED)
        ->and($task->fresh()->completed_at)->not->toBeNull()
        ->and($subtask1->fresh()->is_completed)->toBeTrue()
        ->and($subtask2->fresh()->is_completed)->toBeTrue();
});

test('reopening a completed task clears completed_at and sets pending', function () {
    $task = Task::factory()->completed()->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/tasks/{$task->uuid}/reopen");

    $response->assertStatus(200)
        ->assertJsonPath('task.status', 'pending');

    expect($task->fresh()->status)->toBe(TaskStatus::PENDING)
        ->and($task->fresh()->completed_at)->toBeNull();
});

test('view today filter returns tasks due today in user timezone', function () {
    $yesterday = now('Asia/Tehran')->subDay()->format('Y-m-d');
    $today = $this->userToday;
    $tomorrow = now('Asia/Tehran')->addDay()->format('Y-m-d');

    Task::factory()->create(['user_id' => $this->user->id, 'due_date' => $yesterday]);
    $todayTask = Task::factory()->create(['user_id' => $this->user->id, 'due_date' => $today]);
    Task::factory()->create(['user_id' => $this->user->id, 'due_date' => $tomorrow]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/tasks?view=today');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $todayTask->uuid);
});

test('view overdue filter returns pending tasks due before user today', function () {
    $yesterday = now('Asia/Tehran')->subDay()->format('Y-m-d');

    $overdueTask = Task::factory()->create(['user_id' => $this->user->id, 'due_date' => $yesterday]);
    Task::factory()->create(['user_id' => $this->user->id, 'due_date' => $this->userToday]);
    // Completed overdue task should not appear in overdue view
    Task::factory()->completed()->create(['user_id' => $this->user->id, 'due_date' => $yesterday]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/tasks?view=overdue');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $overdueTask->uuid);
});

test('view inbox filter returns pending tasks with null due date', function () {
    $inboxTask = Task::factory()->create(['user_id' => $this->user->id, 'due_date' => null]);
    Task::factory()->create(['user_id' => $this->user->id, 'due_date' => $this->userToday]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/tasks?view=inbox');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $inboxTask->uuid);
});

test('counts endpoint returns counts for sidebar badges', function () {
    $yesterday = now('Asia/Tehran')->subDay()->format('Y-m-d');
    $today = $this->userToday;
    $tomorrow = now('Asia/Tehran')->addDay()->format('Y-m-d');

    Task::factory()->create(['user_id' => $this->user->id, 'due_date' => $yesterday]); // overdue
    Task::factory()->create(['user_id' => $this->user->id, 'due_date' => $today]); // today
    Task::factory()->create(['user_id' => $this->user->id, 'due_date' => $tomorrow]); // upcoming
    Task::factory()->create(['user_id' => $this->user->id, 'due_date' => null]); // inbox
    Task::factory()->completed()->create(['user_id' => $this->user->id]); // completed

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/tasks/counts');

    $response->assertStatus(200)
        ->assertJsonPath('counts.overdue', 1)
        ->assertJsonPath('counts.today', 1)
        ->assertJsonPath('counts.upcoming', 1)
        ->assertJsonPath('counts.inbox', 1)
        ->assertJsonPath('counts.completed', 1);
});
