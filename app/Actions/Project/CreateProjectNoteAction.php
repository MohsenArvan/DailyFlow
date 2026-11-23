<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\ProjectNote;
use Illuminate\Support\Facades\DB;

class CreateProjectNoteAction
{
    /** @param array{title: string, body: string} $data */
    public function execute(Project $project, array $data): ProjectNote
    {
        return DB::transaction(function () use ($project, $data) {
            $activeProject = Project::forUser($project->user_id)->lockForUpdate()->findOrFail($project->id);

            return $activeProject->notes()->create($data);
        });
    }
}
