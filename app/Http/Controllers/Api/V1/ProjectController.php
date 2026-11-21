<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Project\CreateProjectAction;
use App\Actions\Project\DeleteProjectAction;
use App\Actions\Project\ReorderProjectsAction;
use App\Actions\Project\ToggleFavoriteProjectAction;
use App\Actions\Project\UpdateProjectAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\ReorderProjectsRequest;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        $query = Project::forUser($user->id)
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => function ($q) {
                    $q->completed();
                },
                'tasks as pending_tasks_count' => function ($q) {
                    $q->pending();
                },
            ])
            ->ordered();

        if ($request->boolean('favorites_only')) {
            $query->favorites();
        }

        if ($request->filled('search')) {
            $search = '%'.$request->query('search').'%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', $search)
                    ->orWhere('description', 'ilike', $search);
            });
        }

        $projects = $query->get();

        return ProjectResource::collection($projects);
    }

    public function store(StoreProjectRequest $request, CreateProjectAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $project = $action->execute($user, $request->validated());

        return response()->json([
            'message' => 'پروژه با موفقیت ایجاد شد.',
            'project' => new ProjectResource($project),
        ], 201);
    }

    public function show(Project $project): ProjectResource
    {
        Gate::authorize('view', $project);

        $project->loadCount([
            'tasks',
            'tasks as completed_tasks_count' => function ($q) {
                $q->completed();
            },
            'tasks as pending_tasks_count' => function ($q) {
                $q->pending();
            },
        ]);

        return new ProjectResource($project);
    }

    public function update(UpdateProjectRequest $request, Project $project, UpdateProjectAction $action): JsonResponse
    {
        Gate::authorize('update', $project);

        $updated = $action->execute($project, $request->validated());

        return response()->json([
            'message' => 'پروژه با موفقیت ویرایش شد.',
            'project' => new ProjectResource($updated),
        ]);
    }

    public function destroy(Request $request, Project $project, DeleteProjectAction $action): JsonResponse
    {
        Gate::authorize('delete', $project);

        $deleteTasks = $request->boolean('delete_tasks', false);
        $action->execute($project, $deleteTasks);

        return response()->json([
            'message' => 'پروژه با موفقیت حذف شد.',
        ]);
    }

    public function reorder(ReorderProjectsRequest $request, ReorderProjectsAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $action->execute($user, $request->validated('project_uuids'));

        return response()->json([
            'message' => 'ترتیب پروژه‌ها با موفقیت ذخیره شد.',
        ]);
    }

    public function toggleFavorite(Project $project, ToggleFavoriteProjectAction $action): JsonResponse
    {
        Gate::authorize('update', $project);

        $toggled = $action->execute($project);

        return response()->json([
            'message' => $toggled->is_favorite ? 'پروژه به علاقه‌مندی‌ها اضافه شد.' : 'پروژه از علاقه‌مندی‌ها حذف شد.',
            'project' => new ProjectResource($toggled),
        ]);
    }
}
