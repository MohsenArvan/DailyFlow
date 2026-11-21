<?php

namespace App\Http\Resources;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User|null $user */
        $user = $request->user();
        $userTz = $user?->timezone ?? 'UTC';
        $userToday = now($userTz)->format('Y-m-d');

        $dueDateStr = $this->due_date instanceof CarbonInterface
            ? $this->due_date->format('Y-m-d')
            : ($this->due_date ? (string) $this->due_date : null);

        $subtasksCount = $this->relationLoaded('subtasks') ? $this->subtasks->count() : $this->subtasks()->count();
        $subtasksCompletedCount = $this->relationLoaded('subtasks')
            ? $this->subtasks->where('is_completed', true)->count()
            : $this->subtasks()->where('is_completed', true)->count();

        $subtasksProgress = $subtasksCount > 0
            ? (int) round(($subtasksCompletedCount / $subtasksCount) * 100)
            : ($this->isCompleted() ? 100 : 0);

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'project_id' => $this->project_id,
            'tags' => $this->tags ?? [],
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'priority' => $this->priority->value,
            'priority_label' => $this->priority->label(),
            'due_date' => $dueDateStr,
            'due_time' => $this->due_time,
            'is_today' => $this->isToday($userToday),
            'is_overdue' => $this->isOverdue($userToday),
            'order' => $this->order,
            'recurrence_rule' => $this->recurrence_rule ? Arr::except($this->recurrence_rule, ['anchor_day']) : null,
            'reminder_at' => $this->reminder_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'subtasks_count' => $subtasksCount,
            'subtasks_completed_count' => $subtasksCompletedCount,
            'subtasks_progress' => $subtasksProgress,
            'subtasks' => SubtaskResource::collection($this->whenLoaded('subtasks')),
            'project' => new ProjectResource($this->whenLoaded('project')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
