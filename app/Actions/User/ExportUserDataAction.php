<?php

namespace App\Actions\User;

use App\Models\User;
use Generator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportUserDataAction
{
    public function execute(User $user, string $format): StreamedResponse
    {
        return response()->streamDownload(function () use ($user, $format) {
            if ($format === 'csv') {
                $output = fopen('php://output', 'w');
                fputcsv($output, ['type', 'id', 'parent_id', 'title', 'data'], escape: '');
                foreach ($this->records($user) as [$type, $id, $parent, $title, $data]) {
                    $safeTitle = preg_match('/^[\s]*[=+@\-]|^[\t\r\n]/u', $title) ? "'".$title : $title;
                    fputcsv($output, [$type, $id, $parent, $safeTitle, json_encode($data, JSON_THROW_ON_ERROR)], escape: '');
                }
                fclose($output);

                return;
            }

            echo '{"records":[';
            $separator = '';
            foreach ($this->records($user) as [$type, $id, $parent, $title, $data]) {
                echo $separator.json_encode(compact('type', 'id', 'parent', 'title', 'data'), JSON_THROW_ON_ERROR);
                $separator = ',';
            }
            echo ']}';
        }, "dailyflow.{$format}", [
            'Content-Type' => $format === 'csv' ? 'text/csv; charset=UTF-8' : 'application/json',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    private function records(User $user): Generator
    {
        foreach ($user->projects()->reorder()->lazyById() as $project) {
            yield ['project', $project->id, null, $project->name, $project->toArray()];
        }

        foreach ($user->tasks()->reorder()->with('subtasks')->lazyById() as $task) {
            yield ['task', $task->id, $task->project_id, $task->title, $task->attributesToArray()];

            foreach ($task->subtasks as $subtask) {
                yield ['subtask', $subtask->id, $task->id, $subtask->title, $subtask->toArray()];
            }
        }
    }
}
