<?php

namespace App\Actions\Subtask;

use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateSubtaskAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Task $task, User $user, array $data): Subtask
    {
        return DB::transaction(function () use ($task, $user, $data) {
            $maxPosition = $task->subtasks()->max('position') ?? 0;

            return Subtask::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'title' => trim($data['title']),
                'is_completed' => false,
                'position' => $maxPosition + 1,
            ]);
        });
    }
}
