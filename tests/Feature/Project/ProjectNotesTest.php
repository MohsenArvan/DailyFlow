<?php

use App\Actions\Project\CreateProjectNoteAction;
use App\Models\Project;
use App\Models\ProjectNote;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->project = Project::factory()->for($this->owner)->create();
    $this->url = "/api/v1/projects/{$this->project->uuid}/notes";
});

test('project notes require authentication', function () {
    $this->getJson($this->url)->assertUnauthorized();
});

test('the project owner sees an empty paginated list', function () {
    $this->actingAs($this->owner, 'sanctum')->getJson($this->url)
        ->assertOk()->assertJsonCount(0, 'data')->assertJsonPath('meta.per_page', 20);
});

test('another user cannot list a project’s notes', function () {
    $this->actingAs(User::factory()->create(), 'sanctum')->getJson($this->url)->assertForbidden();
});

test('notes are scoped to the project and pagination orders pinned notes first with stable ties', function () {
    $this->freezeTime();
    $other = Project::factory()->for($this->owner)->create();
    $other->notes()->create(['title' => 'خارج از پروژه', 'body' => 'متن']);
    $pinned = ProjectNote::factory()->for($this->project)->create(['title' => 'سنجاق', 'body' => 'متن', 'pinned_at' => now()]);
    foreach (range(1, 20) as $number) {
        $this->project->notes()->create(['title' => "یادداشت $number", 'body' => 'متن']);
    }

    $this->actingAs($this->owner, 'sanctum')->getJson($this->url)
        ->assertOk()->assertJsonCount(20, 'data')->assertJsonPath('meta.total', 21)
        ->assertJsonPath('data.0.uuid', $pinned->uuid)->assertJsonPath('data.1.title', 'یادداشت 20');

    $this->getJson($this->url.'?page=2')->assertOk()
        ->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'یادداشت 1');
});

test('the owner can create, edit and delete a note', function () {
    $this->actingAs($this->owner, 'sanctum');
    $created = $this->postJson($this->url, ['title' => '  بررسی  ', 'body' => "خط اول\nخط دوم"])
        ->assertCreated()->assertJsonPath('data.title', 'بررسی')
        ->assertJsonPath('data.body', "خط اول\nخط دوم");
    $url = $this->url.'/'.$created->json('data.uuid');

    $this->patchJson($url, ['title' => 'اصلاح'])
        ->assertOk()->assertJsonPath('data.title', 'اصلاح')->assertJsonPath('data.body', "خط اول\nخط دوم");
    $this->deleteJson($url)->assertNoContent();
    $this->getJson($this->url)->assertJsonCount(0, 'data');
});

test('a repeated pin preserves the original pin and edit timestamps', function () {
    $this->freezeTime();
    $note = ProjectNote::factory()->for($this->project)->create();
    $url = $this->url.'/'.$note->uuid;
    $first = $this->actingAs($this->owner, 'sanctum')->patchJson($url, ['is_pinned' => true])->assertOk();
    expect($first->json('data.pinned_at'))->not->toBeNull();

    $this->travel(5)->minutes();
    $this->patchJson($url, ['is_pinned' => true])->assertOk()
        ->assertJsonPath('data.pinned_at', $first->json('data.pinned_at'))
        ->assertJsonPath('data.updated_at', $first->json('data.updated_at'));
    $this->patchJson($url, ['is_pinned' => false])->assertOk()->assertJsonPath('data.pinned_at', null);
});

test('invalid note content and empty patches are rejected', function () {
    $this->actingAs($this->owner, 'sanctum')->postJson($this->url, ['title' => '   ', 'body' => str_repeat('الف', 10001)])
        ->assertUnprocessable()->assertJsonValidationErrors(['title', 'body']);
    $this->postJson($this->url, ['title' => str_repeat('ب', 121), 'body' => '   '])
        ->assertUnprocessable()->assertJsonValidationErrors(['title', 'body']);
    $note = ProjectNote::factory()->for($this->project)->create();
    $this->patchJson($this->url.'/'.$note->uuid, ['project_id' => 1])
        ->assertUnprocessable()->assertJsonValidationErrors(['title']);
});

test('a note from another project cannot be edited or deleted through this project', function () {
    $other = Project::factory()->for($this->owner)->create();
    $note = ProjectNote::factory()->for($other)->create();
    $url = $this->url.'/'.$note->uuid;
    $this->actingAs($this->owner, 'sanctum')->patchJson($url, ['title' => 'تغییر'])->assertNotFound();
    $this->deleteJson($url)->assertNotFound();
    expect($note->fresh()->title)->toBe($note->title);
});

test('another user cannot mutate the project’s notes', function () {
    $note = ProjectNote::factory()->for($this->project)->create();
    $url = $this->url.'/'.$note->uuid;
    $this->actingAs(User::factory()->create(), 'sanctum')->postJson($this->url, ['title' => 'عنوان', 'body' => 'متن'])
        ->assertForbidden();
    $this->patchJson($url, ['title' => 'تغییر'])->assertForbidden();
    $this->deleteJson($url)->assertForbidden();
});

test('deleting a project deletes its notes regardless of task handling', function (bool $deleteTasks) {
    $note = ProjectNote::factory()->for($this->project)->create();
    $this->actingAs($this->owner, 'sanctum')
        ->deleteJson("/api/v1/projects/{$this->project->uuid}", ['delete_tasks' => $deleteTasks])->assertOk();
    $this->assertDatabaseMissing('project_notes', ['id' => $note->id]);
})->with([true, false]);

test('a previously resolved project cannot get new notes after deletion', function () {
    $staleProject = clone $this->project;
    $this->project->delete();

    expect(fn () => app(CreateProjectNoteAction::class)
        ->execute($staleProject, ['title' => 'عنوان', 'body' => 'متن']))
        ->toThrow(ModelNotFoundException::class);
    $this->assertDatabaseCount('project_notes', 0);
});

test('new and existing notes default to unreviewed and the owner can change the review status', function () {
    $this->actingAs($this->owner, 'sanctum');
    $note = ProjectNote::factory()->for($this->project)->create();
    $url = $this->url.'/'.$note->uuid;

    $this->getJson($this->url)->assertOk()->assertJsonPath('data.0.review_status', 'unreviewed');
    $this->postJson($this->url, ['title' => 'یادداشت تازه', 'body' => 'متن'])
        ->assertCreated()->assertJsonPath('data.review_status', 'unreviewed');

    foreach (['in_review', 'reviewed', 'unreviewed'] as $reviewStatus) {
        $this->patchJson($url, ['review_status' => $reviewStatus])
            ->assertOk()->assertJsonPath('data.review_status', $reviewStatus);
        $this->assertDatabaseHas('project_notes', ['uuid' => $note->uuid, 'review_status' => $reviewStatus]);
    }

    $this->patchJson($url, ['review_status' => 'unknown'])
        ->assertUnprocessable()->assertJsonValidationErrors('review_status');
});
