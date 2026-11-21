<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReorderProjectsAction
{
    /**
     * @param  list<string>  $projectUuids
     */
    public function execute(User $user, array $projectUuids): void
    {
        DB::transaction(function () use ($user, $projectUuids) {
            foreach ($projectUuids as $position => $uuid) {
                Project::forUser($user->id)
                    ->where('uuid', $uuid)
                    ->update(['order' => $position + 1]);
            }
        });
    }
}
