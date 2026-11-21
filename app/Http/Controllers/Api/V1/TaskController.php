<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Task\CompleteTaskAction;
use App\Actions\Task\CreateTaskAction;
use App\Actions\Task\DeleteTaskAction;
use App\Actions\Task\ReopenTaskAction;
use App\Actions\Task\ReorderTasksAction;
use App\Actions\Task\UpdateTaskAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\ReorderTasksRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();
        $userTz = $user->timezone ?? 'UTC';
        $userToday = now($userTz)->format('Y-m-d');

        $query = Task::forUser($user->id)
            ->with('subtasks')
            ->orderBy('order', 'asc');

        // Apply view filter
        $view = $request->query('view', 'all');
        switch ($view) {
            case 'today':
                $query->today($userToday);
                break;
            case 'inbox':
                $query->inbox();
                break;
            case 'upcoming':
                $days = (int) $request->query('days', 7);
                $query->upcoming($userToday, $days);
                break;
            case 'overdue':
                $query->overdue($userToday);
                break;
            case 'completed':
                $query->completed()->orderBy('completed_at', 'desc');
                break;
            default:
                if ($request->has('status')) {
                    $query->where('status', $request->query('status'));
                }
                break;
        }

        // Priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->query('priority'));
        }

        // Project filter
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->query('project_id'));
        }

        // Search query
        if ($request->filled('search')) {
            $search = '%'.$request->query('search').'%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', $search)
                    ->orWhere('description', 'ilike', $search);
            });
        }

        $tasks = $query->get();

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request, CreateTaskAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $task = $action->execute($user, $request->validated());

        return response()->json([
            'message' => 'تسک با موفقیت ایجاد شد.',
            'task' => new TaskResource($task->load('subtasks')),
        ], 201);
    }

    public function show(Task $task): TaskResource
    {
        Gate::authorize('view', $task);

        return new TaskResource($task->load('subtasks'));
    }

    public function update(UpdateTaskRequest $request, Task $task, UpdateTaskAction $action): JsonResponse
    {
        Gate::authorize('update', $task);

        $updatedTask = $action->execute($task, $request->validated());

        return response()->json([
            'message' => 'تسک با موفقیت به‌روزرسانی شد.',
            'task' => new TaskResource($updatedTask),
        ]);
    }

    public function destroy(Task $task, DeleteTaskAction $action): JsonResponse
    {
        Gate::authorize('delete', $task);

        $action->execute($task);

        return response()->json([
            'message' => 'تسک با موفقیت حذف شد.',
        ]);
    }

    public function complete(Task $task, CompleteTaskAction $action): JsonResponse
    {
        Gate::authorize('update', $task);

        $completedTask = $action->execute($task);

        return response()->json([
            'message' => 'تسک با موفقیت تکمیل شد.',
            'task' => new TaskResource($completedTask),
        ]);
    }

    public function reopen(Task $task, ReopenTaskAction $action): JsonResponse
    {
        Gate::authorize('update', $task);

        $reopenedTask = $action->execute($task);

        return response()->json([
            'message' => 'تسک مجدداً بازگشایی شد.',
            'task' => new TaskResource($reopenedTask),
        ]);
    }

    public function reorder(ReorderTasksRequest $request, ReorderTasksAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $action->execute($user, $request->validated('task_uuids'));

        return response()->json([
            'message' => 'ترتیب تسک‌ها با موفقیت ذخیره شد.',
        ]);
    }

    public function counts(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $userTz = $user->timezone ?? 'UTC';
        $userToday = now($userTz)->format('Y-m-d');

        $base = Task::forUser($user->id);

        $counts = [
            'today' => (clone $base)->today($userToday)->count(),
            'inbox' => (clone $base)->inbox()->count(),
            'upcoming' => (clone $base)->upcoming($userToday)->count(),
            'overdue' => (clone $base)->overdue($userToday)->count(),
            'completed' => (clone $base)->completed()->count(),
            'today_completed' => (clone $base)->completed()->whereDate('completed_at', $userToday)->count(),
        ];

        return response()->json(['counts' => $counts]);
    }
}
