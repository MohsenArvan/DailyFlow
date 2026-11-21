<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateProjectAction
{
    public function execute(User $user, array $data): Project
    {
        return DB::transaction(function () use ($user, $data) {
            $maxOrder = (int) Project::forUser($user->id)->max('order');

            return Project::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'color' => $data['color'] ?? '#3b82f6',
                'icon' => $data['icon'] ?? 'folder',
                'is_favorite' => $data['is_favorite'] ?? false,
                'view_mode' => $data['view_mode'] ?? 'list',
                'order' => $maxOrder + 1,
            ]);
        });
    }
}
