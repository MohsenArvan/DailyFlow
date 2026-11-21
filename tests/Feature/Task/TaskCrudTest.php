<?php

use App\Enums\TaskPriority;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['timezone' => 'Asia/Tehran']);
    $this->otherUser = User::factory()->create(['timezone' => 'Asia/Tehran']);
});

test('authenticated user can create a task with all fields', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/tasks', [
            'title' => 'تسک آزمایشی',
            'description' => 'توضیحات تکمیلی تسک',
            'priority' => 'urgent',
            'due_date' => '2026-11-25',
            'due_time' => '14:30:00',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('task.title', 'تسک آزمایشی')
        ->assertJsonPath('task.description', 'توضیحات تکمیلی تسک')
        ->assertJsonPath('task.priority', 'urgent')
        ->assertJsonPath('task.status', 'pending')
        ->assertJsonPath('task.due_date', '2026-11-25');

    $this->assertDatabaseHas('tasks', [
        'user_id' => $this->user->id,
        'title' => 'تسک آزمایشی',
        'priority' => 'urgent',
    ]);
});

test('authenticated user can quick-add a task with only a title', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/tasks', [
            'title' => 'تماس با پشتیبانی',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('task.title', 'تماس با پشتیبانی')
        ->assertJsonPath('task.priority', 'medium')
        ->assertJsonPath('task.due_date', null);
});

test('task creation fails when title is missing', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/tasks', [
            'priority' => 'high',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title']);
});

test('user can view their own task', function () {
    $task = Task::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'تسک من',
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/tasks/{$task->uuid}");

    $response->assertStatus(200)
        ->assertJsonPath('data.title', 'تسک من');
});

test('user cannot view another user task', function () {
    $otherTask = Task::factory()->create([
        'user_id' => $this->otherUser->id,
        'title' => 'تسک کاربر دیگر',
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/tasks/{$otherTask->uuid}");

    $response->assertStatus(403);
});

test('user can update their own task', function () {
    $task = Task::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'عنوان قدیمی',
        'priority' => TaskPriority::LOW,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson("/api/v1/tasks/{$task->uuid}", [
            'title' => 'عنوان جدید',
            'priority' => 'high',
            'due_date' => '2026-11-22',
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('task.title', 'عنوان جدید')
        ->assertJsonPath('task.priority', 'high')
        ->assertJsonPath('task.due_date', '2026-11-22');

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'عنوان جدید',
        'priority' => 'high',
    ]);
});

test('user cannot update another user task', function () {
    $otherTask = Task::factory()->create([
        'user_id' => $this->otherUser->id,
        'title' => 'عنوان غیرمجاز',
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson("/api/v1/tasks/{$otherTask->uuid}", [
            'title' => 'دستکاری غیرمجاز',
        ]);

    $response->assertStatus(403);
});

test('user can soft delete their own task', function () {
    $task = Task::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->deleteJson("/api/v1/tasks/{$task->uuid}");

    $response->assertStatus(200);

    $this->assertSoftDeleted('tasks', ['id' => $task->id]);
});

test('user cannot delete another user task', function () {
    $otherTask = Task::factory()->create([
        'user_id' => $this->otherUser->id,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->deleteJson("/api/v1/tasks/{$otherTask->uuid}");

    $response->assertStatus(403);
});

test('user can reorder tasks', function () {
    $task1 = Task::factory()->create(['user_id' => $this->user->id, 'order' => 0]);
    $task2 = Task::factory()->create(['user_id' => $this->user->id, 'order' => 1]);
    $task3 = Task::factory()->create(['user_id' => $this->user->id, 'order' => 2]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/tasks/reorder', [
            'task_uuids' => [$task3->uuid, $task1->uuid, $task2->uuid],
        ]);

    $response->assertStatus(200);

    expect($task3->fresh()->order)->toBe(0)
        ->and($task1->fresh()->order)->toBe(1)
        ->and($task2->fresh()->order)->toBe(2);
});
