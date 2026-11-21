<?php

namespace App\Actions\Task;

use App\Enums\TaskStatus;
use App\Models\Task;

class ReopenTaskAction
{
    public function execute(Task $task): Task
    {
        $task->update([
            'status' => TaskStatus::PENDING,
            'completed_at' => null,
        ]);

        return $task->fresh(['subtasks']);
    }
}
