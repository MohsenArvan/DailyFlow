<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('counts unique local completion days and isolates dashboard metrics', function () {
    $this->travelTo(now()->parse('2026-11-21 12:00:00', 'UTC'));

    $user = User::factory()->create(['timezone' => 'Asia/Tehran']);
    $project = Project::factory()->for($user)->create();
    Sanctum::actingAs($user);

    foreach (['2026-11-20 21:00:00', '2026-11-19 21:00:00', '2026-11-20 06:00:00', '2026-11-15 12:00:00'] as $timestamp) {
        Task::factory()->for($user)->completed()->create(['completed_at' => $timestamp]);
    }

    Task::factory()->for($user)->for($project)->urgent()->create();
    Task::factory()->completed()->create();
    Task::factory()->urgent()->create();

    $this->getJson('/api/v1/dashboard/summary')->assertOk()
        ->assertJsonPath('data.completion_rate', 80)
        ->assertJsonPath('data.current_streak_days', 2)
        ->assertJsonPath('data.best_streak_days', 2)
        ->assertJsonPath('data.priority_distribution.urgent', 1)
        ->assertJsonPath('data.project_distribution.0.name', $project->name)
        ->assertJsonPath('data.project_distribution.0.count', 1);

    $this->getJson('/api/v1/dashboard/velocity')->assertOk()
        ->assertJsonCount(7, 'data.week')
        ->assertJsonCount(30, 'data.month')
        ->assertJsonPath('data.week.5.count', 2)
        ->assertJsonPath('data.week.6.date', '2026-11-21')
        ->assertJsonPath('data.week.6.count', 1);
});
