<?php

namespace App\Jobs;

use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SendTaskReminderJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $userId, public int $taskId) {}

    public function handle(): void
    {
        DB::transaction(function () {
            $task = Task::forUser($this->userId)->pending()
                ->whereNull('reminder_delivered_at')->where('reminder_at', '<=', now())
                ->lockForUpdate()->find($this->taskId);

            if (! $task) {
                return;
            }

            $task->user->notifications()->create([
                'id' => (string) Str::uuid(),
                'type' => 'task-reminder',
                'data' => ['title' => $task->title, 'task_uuid' => $task->uuid],
            ]);
            $task->forceFill(['reminder_delivered_at' => now()])->save();
        });
    }
}
