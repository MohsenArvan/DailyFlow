<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('moves a project task between board columns while preserving agenda and completion behavior', function () {
    $user = User::factory()->create(['timezone' => 'UTC']);
    $project = Project::factory()->for($user)->create();
    $task = Task::factory()->for($user)->for($project)->create([
        'due_date' => today()->toDateString(),
        'recurrence_rule' => ['frequency' => 'daily'],
    ]);
    Sanctum::actingAs($user);
    $url = "/api/v1/tasks/{$task->uuid}";

    $this->putJson($url, ['status' => 'in_progress'])->assertOk()->assertJsonPath('task.status', 'in_progress');
    $this->getJson('/api/v1/tasks?view=today')->assertJsonPath('data.0.uuid', $task->uuid);
    $this->getJson('/api/v1/dashboard/summary')->assertJsonPath('data.project_distribution.0.count', 1);
    $this->putJson($url, ['status' => 'completed'])->assertOk()->assertJsonPath('task.status', 'completed');
    $this->putJson($url, ['status' => 'completed'])->assertOk();
    expect(Task::where('recurrence_source_id', $task->id)->count())->toBe(1);
    $this->putJson($url, ['status' => 'in_progress'])->assertOk()
        ->assertJsonPath('task.completed_at', null);
    $this->putJson($url, ['status' => 'pending'])->assertOk()->assertJsonPath('task.status', 'pending');

    Sanctum::actingAs(User::factory()->create());
    $this->putJson($url, ['status' => 'completed'])->assertForbidden();
});
