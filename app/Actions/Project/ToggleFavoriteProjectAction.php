<?php

namespace App\Actions\Project;

use App\Models\Project;

class ToggleFavoriteProjectAction
{
    public function execute(Project $project): Project
    {
        $project->update([
            'is_favorite' => ! $project->is_favorite,
        ]);

        return $project->fresh();
    }
}
