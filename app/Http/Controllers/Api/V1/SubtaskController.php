<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Subtask\CreateSubtaskAction;
use App\Actions\Subtask\DeleteSubtaskAction;
use App\Actions\Subtask\ReorderSubtasksAction;
use App\Actions\Subtask\ToggleSubtaskAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Subtask\ReorderSubtasksRequest;
use App\Http\Requests\Subtask\StoreSubtaskRequest;
use App\Http\Requests\Subtask\UpdateSubtaskRequest;
use App\Http\Resources\SubtaskResource;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class SubtaskController extends Controller
{
    public function store(StoreSubtaskRequest $request, Task $task, CreateSubtaskAction $action): JsonResponse
    {
        Gate::authorize('update', $task);

        /** @var User $user */
        $user = $request->user();
        $subtask = $action->execute($task, $user, $request->validated());

        return response()->json([
            'message' => 'زیرتسک ایجاد شد.',
            'subtask' => new SubtaskResource($subtask),
        ], 201);
    }

    public function update(UpdateSubtaskRequest $request, Subtask $subtask): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        if ($subtask->user_id !== $user->id) {
            abort(403);
        }

        $subtask->update($request->validated());

        return response()->json([
            'message' => 'زیرتسک به‌روزرسانی شد.',
            'subtask' => new SubtaskResource($subtask->fresh()),
        ]);
    }

    public function toggle(Subtask $subtask, ToggleSubtaskAction $action): JsonResponse
    {
        /** @var User $user */
        $user = request()->user();
        if ($subtask->user_id !== $user->id) {
            abort(403);
        }

        $toggled = $action->execute($subtask);

        return response()->json([
            'message' => 'وضعیت زیرتسک تغییر یافت.',
            'subtask' => new SubtaskResource($toggled),
        ]);
    }

    public function destroy(Subtask $subtask, DeleteSubtaskAction $action): JsonResponse
    {
        /** @var User $user */
        $user = request()->user();
        if ($subtask->user_id !== $user->id) {
            abort(403);
        }

        $action->execute($subtask);

        return response()->json([
            'message' => 'زیرتسک حذف شد.',
        ]);
    }

    public function reorder(ReorderSubtasksRequest $request, Task $task, ReorderSubtasksAction $action): JsonResponse
    {
        Gate::authorize('update', $task);

        $action->execute($task, $request->validated('subtask_ids'));

        return response()->json([
            'message' => 'ترتیب زیرتسک‌ها ذخیره شد.',
        ]);
    }
}
