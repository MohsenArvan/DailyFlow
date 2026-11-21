<?php

namespace App\Actions\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateTaskAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, array $data): Task
    {
        return DB::transaction(function () use ($user, $data) {
            $maxOrder = Task::forUser($user->id)->max('order') ?? 0;

            return Task::create([
                'user_id' => $user->id,
                'project_id' => $data['project_id'] ?? null,
                'parent_id' => $data['parent_id'] ?? null,
                'title' => trim($data['title']),
                'description' => $data['description'] ?? null,
                'status' => TaskStatus::PENDING,
                'priority' => isset($data['priority']) ? TaskPriority::from($data['priority']) : TaskPriority::MEDIUM,
                'due_date' => $data['due_date'] ?? null,
                'due_time' => $data['due_time'] ?? null,
                'order' => $maxOrder + 1,
            ]);
        });
    }
}
