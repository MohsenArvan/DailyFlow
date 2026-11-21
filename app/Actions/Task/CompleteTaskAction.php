<?php

namespace App\Actions\Task;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class CompleteTaskAction
{
    public function __construct(private GenerateNextRecurringTaskAction $generate) {}

    public function execute(Task $task): Task
    {
        return DB::transaction(function () use ($task) {
            $task = Task::forUser($task->user_id)->lockForUpdate()->findOrFail($task->id);
            if ($task->isCompleted()) {
                return $task->load('subtasks');
            }

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

            $this->generate->execute($task);

            return $task->fresh(['subtasks']);
        });
    }
}
