<?php

namespace App\Actions\Task;

use App\Enums\TaskPriority;
use App\Models\Task;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateTaskAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Task $task, array $data): Task
    {
        return DB::transaction(function () use ($task, $data) {
            $task = Task::forUser($task->user_id)->lockForUpdate()->findOrFail($task->id);
            $attributes = [];

            $rule = array_key_exists('recurrence_rule', $data) ? $data['recurrence_rule'] : $task->recurrence_rule;
            $due = array_key_exists('due_date', $data) ? $data['due_date'] : $task->due_date;
            if ($rule && ! $due) {
                throw ValidationException::withMessages(['due_date' => 'برای تکرار، تاریخ سررسید لازم است.']);
            }

            if ($rule && array_key_exists('due_date', $data) && $due !== $task->due_date?->toDateString()) {
                $attributes['recurrence_rule'] = Arr::except($rule, ['anchor_day']);
            } elseif (array_key_exists('recurrence_rule', $data)) {
                $previous = $task->recurrence_rule ?? [];
                if ($rule && ($rule['frequency'] ?? null) === ($previous['frequency'] ?? null) && isset($previous['anchor_day'])) {
                    $rule['anchor_day'] = $previous['anchor_day'];
                }
                $attributes['recurrence_rule'] = $rule;
            }
            if (array_key_exists('reminder_at', $data)) {
                $reminder = isset($data['reminder_at']) ? CarbonImmutable::parse($data['reminder_at'])->utc() : null;
                if ($reminder?->toIso8601String() !== $task->reminder_at?->utc()->toIso8601String()) {
                    $attributes['reminder_at'] = $reminder;
                    $task->reminder_delivered_at = null;
                }
            }

            if (array_key_exists('title', $data)) {
                $attributes['title'] = trim($data['title']);
            }
            if (array_key_exists('tags', $data)) {
                $attributes['tags'] = array_values($data['tags'] ?? []);
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
