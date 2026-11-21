<?php

namespace App\Actions\Task;

use App\Enums\TaskPriority;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class UpdateTaskAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Task $task, array $data): Task
    {
        return DB::transaction(function () use ($task, $data) {
            $attributes = [];

            if (array_key_exists('title', $data)) {
                $attributes['title'] = trim($data['title']);
            }
            if (array_key_exists('description', $data)) {
                $attributes['description'] = $data['description'];
            }
            if (array_key_exists('priority', $data)) {
                $attributes['priority'] = TaskPriority::from($data['priority']);
            }
            if (array_key_exists('due_date', $data)) {
                $attributes['due_date'] = $data['due_date'];
            }
            if (array_key_exists('due_time', $data)) {
                $attributes['due_time'] = $data['due_time'];
            }
            if (array_key_exists('project_id', $data)) {
                $attributes['project_id'] = $data['project_id'];
            }

            $task->update($attributes);

            return $task->fresh(['subtasks']);
        });
    }
}
