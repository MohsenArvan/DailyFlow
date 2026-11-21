<?php

namespace App\Services\Analytics;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class ProductivityMetricsService
{
    public function summary(User $user): array
    {
        $tasks = Task::forUser($user->id);
        $counts = (clone $tasks)->selectRaw('status, priority, COUNT(*) AS total')
            ->groupBy('status', 'priority')->get();

        $total = 0;
        $completed = 0;
        $priorities = array_fill_keys(array_column(TaskPriority::cases(), 'value'), 0);
        foreach ($counts as $row) {
            $total += $row->total;
            $completed += $row->status === TaskStatus::COMPLETED ? $row->total : 0;
            $priorities[$row->priority->value] += $row->total;
        }

        $today = CarbonImmutable::today($user->timezone ?: 'UTC');
        $days = $this->completionDays($user, $today);
        $run = $best = 0;
        $previous = null;
        foreach (array_keys($days) as $date) {
            $day = CarbonImmutable::parse($date, $today->timezone);
            $run = $previous === $day->subDay()->toDateString() ? $run + 1 : 1;
            $best = max($best, $run);
            $previous = $date;
        }

        $projects = $user->projects()->reorder()->orderBy('name')
            ->withCount(['tasks as active_count' => fn ($query) => $query->forUser($user->id)->pending()])
            ->get()->map(fn ($project) => [
                'id' => $project->uuid,
                'name' => $project->name,
                'color' => $project->color,
                'count' => $project->active_count,
            ])->values();

        return [
            'completion_rate' => $total ? round(100 * $completed / $total, 1) : 0,
            'current_streak_days' => in_array($previous, [$today->toDateString(), $today->subDay()->toDateString()], true) ? $run : 0,
            'best_streak_days' => $best,
            'priority_distribution' => $priorities,
            'project_distribution' => $projects,
            'completed_today' => $days[$today->toDateString()] ?? 0,
            'daily_goal' => $user->preferences['daily_goal'],
        ];
    }

    public function velocity(User $user): array
    {
        $today = CarbonImmutable::today($user->timezone ?: 'UTC');
        $start = $today->subDays(29);
        $days = $this->completionDays($user, $today, $start);
        $month = [];
        for ($day = $start; $day <= $today; $day = $day->addDay()) {
            $date = $day->toDateString();
            $month[] = ['date' => $date, 'count' => $days[$date] ?? 0];
        }

        return ['week' => array_slice($month, -7), 'month' => $month];
    }

    private function completionDays(User $user, CarbonImmutable $today, ?CarbonImmutable $start = null): array
    {
        $query = Task::forUser($user->id)->completed()
            ->where('completed_at', '<', $today->addDay()->utc());
        if ($start !== null) {
            $query->where('completed_at', '>=', $start->utc());
        }

        if (DB::connection()->getDriverName() === 'pgsql') {
            return $query->toBase()
                ->selectRaw('DATE(completed_at AT TIME ZONE ?) AS local_day, COUNT(*) AS total', [$today->timezoneName])
                ->groupBy('local_day')->orderBy('local_day')->pluck('total', 'local_day')
                ->map(fn ($count) => (int) $count)->all();
        }

        $days = [];
        foreach ($query->orderBy('completed_at')->toBase()->cursor() as $task) {
            $date = CarbonImmutable::parse($task->completed_at, 'UTC')->setTimezone($today->timezone)->toDateString();
            $days[$date] = ($days[$date] ?? 0) + 1;
        }

        return $days;
    }
}
