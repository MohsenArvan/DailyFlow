<?php

namespace App\Actions\Subtask;

use App\Models\Subtask;

class DeleteSubtaskAction
{
    public function execute(Subtask $subtask): bool
    {
        return (bool) $subtask->delete();
    }
}
