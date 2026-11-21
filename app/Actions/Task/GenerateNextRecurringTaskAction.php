<?php

namespace App\Actions\Task;

use App\Enums\TaskStatus;
use App\Models\Task;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class GenerateNextRecurringTaskAction
{
    public function execute(Task $task): void
    {
        DB::transaction(function () use ($task) {
            $source = Task::forUser($task->user_id)->lockForUpdate()->find($task->id);
            if (! $source?->isCompleted() || ! $source->due_date || ! $source->recurrence_rule
                || Task::withTrashed()->forUser($source->user_id)->where('recurrence_source_id', $source->id)->exists()) {
                return;
            }

            $rule = $source->recurrence_rule;
            $date = $source->due_date;
            $interval = max(1, (int) ($rule['interval'] ?? 1));
            $anchorDay = (int) ($rule['anchor_day'] ?? $date->day);
            $next = match ($rule['frequency'] ?? null) {
                'daily' => $date->addDays($interval),
                'weekdays' => $date->addWeekdays($interval),
                'weekly' => $date->addWeeks($interval),
                'monthly' => $date->startOfMonth()->addMonths($interval),
                'yearly' => $date->startOfMonth()->addYears($interval),
                'custom' => $this->nextCustomDate($source, $interval, $rule['by_weekday'] ?? []),
                default => null,
            };

            if ($next && in_array($rule['frequency'], ['monthly', 'yearly'], true)) {
                $next = $next->day(min($anchorDay, $next->daysInMonth));
                $rule['anchor_day'] = $anchorDay;
            }

            if (! $next || (isset($rule['end_date']) && $next->toDateString() > $rule['end_date'])) {
                return;
            }

            $copy = $source->replicate(['uuid', 'completed_at', 'reminder_at', 'deleted_at', 'reminder_delivered_at']);
            $copy->forceFill([
                'recurrence_rule' => $rule,
                'recurrence_source_id' => $source->id,
                'status' => TaskStatus::PENDING,
                'due_date' => $next->toDateString(),
            ])->save();

            $copy->subtasks()->createMany($source->subtasks->map->only(['title', 'position'])->all());
        });
    }

    private function nextCustomDate(Task $task, int $interval, array $weekdays): ?CarbonImmutable
    {
        $date = $task->due_date;
        $weekdays = array_intersect(range(0, 6), array_map('intval', $weekdays));
        if ($weekdays === []) {
            return null;
        }

        $weekStart = $date->startOfWeek(1);
        for ($next = $date->addDay(); ; $next = $next->addDay()) {
            $weeks = intdiv((int) $weekStart->diffInDays($next), 7);
            if ($weeks % $interval === 0 && in_array($next->dayOfWeek, $weekdays, true)) {
                return $next;
            }
        }
    }
}
