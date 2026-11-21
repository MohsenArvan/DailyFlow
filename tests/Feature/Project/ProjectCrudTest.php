<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

test('authenticated user can list their projects', function () {
    Project::factory()->count(3)->create(['user_id' => $this->user->id]);
    Project::factory()->count(2)->create(['user_id' => $this->otherUser->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/projects');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('user can filter favorite projects only', function () {
    Project::factory()->favorite()->create(['user_id' => $this->user->id, 'name' => 'Fav 1']);
    Project::factory()->create(['user_id' => $this->user->id, 'name' => 'Normal 1']);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/projects?favorites_only=1');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Fav 1');
});

test('authenticated user can create project with valid inputs', function () {
    $payload = [
        'name' => 'پروژه بازاریابی',
        'description' => 'توضیحات پروژه مارکتینگ',
        'color' => '#10b981',
        'icon' => 'briefcase',
        'is_favorite' => true,
    ];

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/projects', $payload);

    $response->assertStatus(201)
        ->assertJsonPath('project.name', 'پروژه بازاریابی')
        ->assertJsonPath('project.color', '#10b981')
        ->assertJsonPath('project.icon', 'briefcase')
        ->assertJsonPath('project.is_favorite', true);

    $this->assertDatabaseHas('projects', [
        'user_id' => $this->user->id,
        'name' => 'پروژه بازاریابی',
    ]);
});

test('project creation fails when name is missing', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/projects', [
            'color' => '#10b981',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('project creation fails with invalid color format', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/projects', [
            'name' => 'تست رنگ اشتباه',
            'color' => 'not-a-color',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['color']);
});

test('user can view their own project', function () {
    $project = Project::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/projects/{$project->uuid}");

    $response->assertStatus(200)
        ->assertJsonPath('data.uuid', $project->uuid);
});

test('user cannot view another user project', function () {
    $otherProject = Project::factory()->create(['user_id' => $this->otherUser->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/projects/{$otherProject->uuid}");

    $response->assertStatus(403);
});

test('user can update their own project', function () {
    $project = Project::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'نام قدیمی',
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson("/api/v1/projects/{$project->uuid}", [
            'name' => 'نام جدید',
            'color' => '#ef4444',
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('project.name', 'نام جدید')
        ->assertJsonPath('project.color', '#ef4444');

    expect($project->fresh()->name)->toBe('نام جدید')
        ->and($project->fresh()->color)->toBe('#ef4444');
});

test('user cannot update another user project', function () {
    $otherProject = Project::factory()->create(['user_id' => $this->otherUser->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson("/api/v1/projects/{$otherProject->uuid}", [
            'name' => 'هک نام',
        ]);

    $response->assertStatus(403);
});

test('user can toggle favorite on project', function () {
    $project = Project::factory()->create([
        'user_id' => $this->user->id,
        'is_favorite' => false,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/projects/{$project->uuid}/favorite");

    $response->assertStatus(200)
        ->assertJsonPath('project.is_favorite', true);

    expect($project->fresh()->is_favorite)->toBeTrue();

    // Toggle back
    $response2 = $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/projects/{$project->uuid}/favorite");

    $response2->assertStatus(200)
        ->assertJsonPath('project.is_favorite', false);

    expect($project->fresh()->is_favorite)->toBeFalse();
});

test('user can reorder projects', function () {
    $p1 = Project::factory()->create(['user_id' => $this->user->id, 'order' => 1]);
    $p2 = Project::factory()->create(['user_id' => $this->user->id, 'order' => 2]);
    $p3 = Project::factory()->create(['user_id' => $this->user->id, 'order' => 3]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/projects/reorder', [
            'project_uuids' => [$p3->uuid, $p1->uuid, $p2->uuid],
        ]);

    $response->assertStatus(200);

    expect($p3->fresh()->order)->toBe(1)
        ->and($p1->fresh()->order)->toBe(2)
        ->and($p2->fresh()->order)->toBe(3);
});

test('user can delete their own project', function () {
    $project = Project::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->deleteJson("/api/v1/projects/{$project->uuid}");

    $response->assertStatus(200);
    $this->assertSoftDeleted('projects', ['id' => $project->id]);
});

test('user cannot delete another user project', function () {
    $otherProject = Project::factory()->create(['user_id' => $this->otherUser->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->deleteJson("/api/v1/projects/{$otherProject->uuid}");

    $response->assertStatus(403);
});
