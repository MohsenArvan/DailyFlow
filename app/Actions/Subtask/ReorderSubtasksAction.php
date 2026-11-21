<?php

namespace App\Actions\Subtask;

use App\Models\Task;
use Illuminate\Support\Facades\DB;

class ReorderSubtasksAction
{
    /**
     * @param  array<int, int>  $subtaskIds
     */
    public function execute(Task $task, array $subtaskIds): void
    {
        DB::transaction(function () use ($task, $subtaskIds) {
            foreach ($subtaskIds as $index => $id) {
                $task->subtasks()
                    ->where('id', $id)
                    ->update(['position' => $index]);
            }
        });
    }
}
