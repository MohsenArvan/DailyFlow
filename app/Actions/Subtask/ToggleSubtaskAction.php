<?php

namespace App\Actions\Subtask;

use App\Models\Subtask;

class ToggleSubtaskAction
{
    public function execute(Subtask $subtask): Subtask
    {
        $newStatus = ! $subtask->is_completed;

        $subtask->update([
            'is_completed' => $newStatus,
            'completed_at' => $newStatus ? now() : null,
        ]);

        return $subtask->fresh();
    }
}
