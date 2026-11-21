<?php

namespace App\Actions\Task;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReorderTasksAction
{
    /**
     * @param  array<int, string>  $taskUuids
     */
    public function execute(User $user, array $taskUuids): void
    {
        DB::transaction(function () use ($user, $taskUuids) {
            foreach ($taskUuids as $index => $uuid) {
                Task::forUser($user->id)
                    ->where('uuid', $uuid)
                    ->update(['order' => $index]);
            }
        });
    }
}
