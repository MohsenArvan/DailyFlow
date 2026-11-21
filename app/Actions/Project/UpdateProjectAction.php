<?php

namespace App\Actions\Project;

use App\Models\Project;

class UpdateProjectAction
{
    public function execute(Project $project, array $data): Project
    {
        $project->update(array_filter([
            'name' => $data['name'] ?? null,
            'description' => array_key_exists('description', $data) ? $data['description'] : null,
            'color' => $data['color'] ?? null,
            'icon' => array_key_exists('icon', $data) ? $data['icon'] : null,
            'is_favorite' => array_key_exists('is_favorite', $data) ? (bool) $data['is_favorite'] : null,
            'view_mode' => $data['view_mode'] ?? null,
        ], fn ($val) => $val !== null));

        return $project->fresh();
    }
}
