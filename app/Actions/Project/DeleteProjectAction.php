<?php

namespace App\Actions\Project;

use App\Models\Project;
use Illuminate\Support\Facades\DB;

class DeleteProjectAction
{
    public function execute(Project $project, bool $deleteTasks = false): void
    {
        DB::transaction(function () use ($project, $deleteTasks) {
            $project = Project::forUser($project->user_id)->lockForUpdate()->findOrFail($project->id);
            $project->notes()->delete();

            if ($deleteTasks) {
                $project->tasks()->delete();
            } else {
                $project->tasks()->update(['project_id' => null]);
            }

            $project->delete();
        });
    }
}
