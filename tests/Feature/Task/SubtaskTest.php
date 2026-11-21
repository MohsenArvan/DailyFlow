<?php

use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
    $this->task = Task::factory()->create(['user_id' => $this->user->id]);
});

test('user can add subtask to their task', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/tasks/{$this->task->uuid}/subtasks", [
            'title' => 'قدم اول کار',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('subtask.title', 'قدم اول کار')
        ->assertJsonPath('subtask.is_completed', false);

    $this->assertDatabaseHas('subtasks', [
        'task_id' => $this->task->id,
        'user_id' => $this->user->id,
        'title' => 'قدم اول کار',
    ]);
});

test('user cannot add subtask to another user task', function () {
    $otherTask = Task::factory()->create(['user_id' => $this->otherUser->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/tasks/{$otherTask->uuid}/subtasks", [
            'title' => 'زیرتسک غیرمجاز',
        ]);

    $response->assertStatus(403);
});

test('user can toggle subtask completion', function () {
    $subtask = Subtask::factory()->create([
        'task_id' => $this->task->id,
        'user_id' => $this->user->id,
        'is_completed' => false,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/subtasks/{$subtask->id}/toggle");

    $response->assertStatus(200)
        ->assertJsonPath('subtask.is_completed', true);

    expect($subtask->fresh()->is_completed)->toBeTrue();

    // Toggle back
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/subtasks/{$subtask->id}/toggle");

    $response->assertStatus(200)
        ->assertJsonPath('subtask.is_completed', false);

    expect($subtask->fresh()->is_completed)->toBeFalse();
});

test('user can update subtask title', function () {
    $subtask = Subtask::factory()->create([
        'task_id' => $this->task->id,
        'user_id' => $this->user->id,
        'title' => 'عنوان قدیمی',
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson("/api/v1/subtasks/{$subtask->id}", [
            'title' => 'عنوان ویرایش‌شده',
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('subtask.title', 'عنوان ویرایش‌شده');

    expect($subtask->fresh()->title)->toBe('عنوان ویرایش‌شده');
});

test('user can delete subtask', function () {
    $subtask = Subtask::factory()->create([
        'task_id' => $this->task->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->deleteJson("/api/v1/subtasks/{$subtask->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('subtasks', ['id' => $subtask->id]);
});

test('user can reorder subtasks', function () {
    $s1 = Subtask::factory()->create(['task_id' => $this->task->id, 'user_id' => $this->user->id, 'position' => 0]);
    $s2 = Subtask::factory()->create(['task_id' => $this->task->id, 'user_id' => $this->user->id, 'position' => 1]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/tasks/{$this->task->uuid}/subtasks/reorder", [
            'subtask_ids' => [$s2->id, $s1->id],
        ]);

    $response->assertStatus(200);

    expect($s2->fresh()->position)->toBe(0)
        ->and($s1->fresh()->position)->toBe(1);
});
