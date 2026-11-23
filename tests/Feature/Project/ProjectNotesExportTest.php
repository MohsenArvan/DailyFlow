<?php

use App\Models\Project;
use App\Models\ProjectNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('exports only the owner’s project notes in JSON and CSV', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $note = ProjectNote::factory()->for($project)->create([
        'title' => '=یادداشت',
        'body' => "خط اول\nخط دوم، «موبایل»",
    ]);
    $otherNote = ProjectNote::factory()->create();
    $deletedProject = Project::factory()->for($user)->create();
    $deletedNote = ProjectNote::factory()->for($deletedProject)->create();
    $deletedProject->delete();

    $this->actingAs($user, 'sanctum');
    $json = $this->get('/api/v1/user/export?format=json')->assertOk()->streamedContent();
    $records = collect(json_decode($json, true, flags: JSON_THROW_ON_ERROR)['records'])
        ->where('type', 'project_note')->values();
    expect($records)->toHaveCount(1)
        ->and($records[0]['parent'])->toBe($project->id)
        ->and($records[0]['title'])->toBe($note->title)
        ->and($records[0]['data']['project_uuid'])->toBe($project->uuid)
        ->and($records[0]['data']['body'])->toBe($note->body)
        ->and($json)->not->toContain($otherNote->uuid, $deletedNote->uuid);

    $csv = $this->get('/api/v1/user/export?format=csv')->assertOk()->streamedContent();
    $stream = fopen('php://temp', 'w+');
    fwrite($stream, $csv);
    rewind($stream);

    $noteRows = [];
    while (($row = fgetcsv($stream, escape: '')) !== false) {
        if ($row[0] === 'project_note') {
            $noteRows[] = $row;
        }
    }
    fclose($stream);

    expect($noteRows)->toHaveCount(1)
        ->and($noteRows[0][3])->toBe("'".$note->title);
    $data = json_decode($noteRows[0][4], true, flags: JSON_THROW_ON_ERROR);
    expect($data['project_uuid'])->toBe($project->uuid)
        ->and($data['body'])->toBe($note->body);
});
