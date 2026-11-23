<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\ProjectNote;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateProjectNoteAction
{
    /** @param array{title?: string, body?: string, is_pinned?: bool|int|string} $data */
    public function execute(Project $project, string $noteUuid, array $data): ProjectNote
    {
        return DB::transaction(function () use ($project, $noteUuid, $data) {
            $note = $project->notes()->where('uuid', $noteUuid)->lockForUpdate()->firstOrFail();
            $note->fill(Arr::only($data, ['title', 'body']));

            if (array_key_exists('is_pinned', $data)) {
                $note->pinned_at = $data['is_pinned'] ? ($note->pinned_at ?? now()) : null;
            }

            if ($note->isDirty()) {
                $note->save();
            }

            return $note;
        });
    }
}
