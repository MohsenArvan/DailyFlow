<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $total = $this->tasks_count ?? $this->tasks()->count();
        $completed = $this->completed_tasks_count ?? $this->tasks()->completed()->count();
        $pending = $this->pending_tasks_count ?? $this->tasks()->active()->count();
        $progress = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'color' => $this->color,
            'icon' => $this->icon,
            'is_favorite' => $this->is_favorite,
            'order' => $this->order,
            'view_mode' => $this->view_mode,
            'tasks_count' => (int) $total,
            'pending_tasks_count' => (int) $pending,
            'completed_tasks_count' => (int) $completed,
            'progress_percentage' => $progress,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
