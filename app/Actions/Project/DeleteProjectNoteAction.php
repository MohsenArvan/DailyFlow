<?php

namespace App\Actions\Project;

use App\Models\Project;

class DeleteProjectNoteAction
{
    public function execute(Project $project, string $noteUuid): void
    {
        $project->notes()->where('uuid', $noteUuid)->firstOrFail()->delete();
    }
}
