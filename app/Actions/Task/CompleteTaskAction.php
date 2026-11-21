<?php

namespace App\Actions\Task;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class CompleteTaskAction
{
    public function execute(Task $task): Task
    {
        return DB::transaction(function () use ($task) {
            $now = now();

            $task->update([
                'status' => TaskStatus::COMPLETED,
                'completed_at' => $now,
            ]);

            // Cascade complete any unfinished subtasks
            $task->subtasks()
                ->where('is_completed', false)
                ->update([
                    'is_completed' => true,
                    'completed_at' => $now,
                ]);

            return $task->fresh(['subtasks']);
        });
    }
}
