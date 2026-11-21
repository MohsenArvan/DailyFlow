<?php

use App\Actions\Task\GenerateNextRecurringTaskAction;
use App\Jobs\SendTaskReminderJob;
use App\Models\Task;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tasks:generate-recurring', function (GenerateNextRecurringTaskAction $generate) {
    Task::completed()->whereNotNull('recurrence_rule')->whereNotNull('due_date')
        ->whereNotExists(function ($query) {
            $query->selectRaw('1')->from('tasks as next')
                ->whereColumn('next.recurrence_source_id', 'tasks.id');
        })->eachById(fn ($task) => $generate->execute($task));
});

Schedule::command('tasks:generate-recurring')->hourly()->withoutOverlapping();

Artisan::command('tasks:check-reminders', function () {
    Task::pending()->whereNull('reminder_delivered_at')->where('reminder_at', '<=', now())
        ->eachById(fn ($task) => SendTaskReminderJob::dispatch($task->user_id, $task->id));
});

Schedule::command('tasks:check-reminders')->everyMinute()->withoutOverlapping();
