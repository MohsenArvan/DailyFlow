<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('creates one next occurrence when a recurring task is completed and reconciled', function () {
    Sanctum::actingAs($user = User::factory()->create());
    $task = $this->postJson('/api/v1/tasks', [
        'title' => 'Recurring task',
        'tags' => ['routine'],
        'due_date' => '2026-11-20',
        'recurrence_rule' => ['frequency' => 'weekdays', 'interval' => 1],
    ])->assertCreated()->json('task.uuid');

    $this->postJson("/api/v1/tasks/{$task}/complete")->assertOk();
    expect(Task::forUser($user->id)->pending()->sole()->due_date->toDateString())->toBe('2026-11-23');
    expect(Task::forUser($user->id)->pending()->sole()->tags)->toBe(['routine']);
    $this->artisan('tasks:generate-recurring')->assertSuccessful();
    $this->postJson("/api/v1/tasks/{$task}/complete")->assertOk();
    expect(Task::forUser($user->id)->count())->toBe(2);
});

it('returns only the current user’s search results and exports', function () {
    Sanctum::actingAs($user = User::factory()->create());
    Task::factory()->for($user)->create(['title' => 'Weekly review']);
    $foreign = Task::factory()->create(['title' => 'Private review']);
    Project::factory()->for($user)->create(['name' => 'Review project']);

    $this->getJson('/api/v1/search?q=REVIEW')->assertOk()
        ->assertJsonCount(1, 'data.tasks')->assertJsonCount(1, 'data.projects')->assertDontSee($foreign->title);
    $response = $this->get('/api/v1/user/export?format=json')->assertOk()->assertDownload('dailyflow.json');
    expect($response->streamedContent())->toContain('Weekly review')->not->toContain($foreign->title);
    $this->get('/api/v1/user/export?format=csv')->assertOk()->assertDownload('dailyflow.csv');
});

it('delivers a reminder once and only to its owner', function () {
    $task = Task::factory()->create(['reminder_at' => now()->subMinute()]);
    $this->artisan('tasks:check-reminders')->assertSuccessful();
    $this->artisan('tasks:check-reminders')->assertSuccessful();

    Sanctum::actingAs(User::factory()->create());
    $this->getJson('/api/v1/notifications/unread')->assertOk()->assertJsonCount(0, 'data');

    Sanctum::actingAs($task->user);
    $id = $this->getJson('/api/v1/notifications/unread')->assertOk()->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.data.task_uuid', $task->uuid)->json('data.0.id');
    $this->patchJson("/api/v1/notifications/{$id}/read")->assertNoContent();
    $this->getJson('/api/v1/notifications/unread')->assertJsonCount(0, 'data');
});

it('preserves the original day of month when an occurrence falls in February', function () {
    Sanctum::actingAs($user = User::factory()->create());
    $january = Task::factory()->for($user)->create([
        'due_date' => '2026-01-31',
        'recurrence_rule' => ['frequency' => 'monthly'],
    ]);

    $this->postJson("/api/v1/tasks/{$january->uuid}/complete")->assertOk();
    $february = Task::forUser($user->id)->pending()->sole();
    expect($february->due_date->toDateString())->toBe('2026-02-28');
    $this->postJson("/api/v1/tasks/{$february->uuid}/complete")->assertOk();
    expect(Task::forUser($user->id)->pending()->sole()->due_date->toDateString())->toBe('2026-03-31');
});
