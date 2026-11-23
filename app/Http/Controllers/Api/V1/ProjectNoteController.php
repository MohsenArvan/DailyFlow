<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Project\CreateProjectNoteAction;
use App\Actions\Project\DeleteProjectNoteAction;
use App\Actions\Project\UpdateProjectNoteAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectNoteRequest;
use App\Http\Requests\Project\UpdateProjectNoteRequest;
use App\Http\Resources\ProjectNoteResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ProjectNoteController extends Controller
{
    public function index(Project $project): AnonymousResourceCollection
    {
        Gate::authorize('view', $project);

        return ProjectNoteResource::collection($project->notes()
            ->orderByRaw('pinned_at IS NULL ASC')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(20));
    }

    public function store(StoreProjectNoteRequest $request, Project $project, CreateProjectNoteAction $action): JsonResponse
    {
        return (new ProjectNoteResource($action->execute($project, $request->validated())))
            ->response()->setStatusCode(201);
    }

    public function update(UpdateProjectNoteRequest $request, Project $project, string $noteUuid, UpdateProjectNoteAction $action): ProjectNoteResource
    {
        return new ProjectNoteResource($action->execute($project, $noteUuid, $request->validated()));
    }

    public function destroy(Project $project, string $noteUuid, DeleteProjectNoteAction $action): Response
    {
        Gate::authorize('update', $project);
        $action->execute($project, $noteUuid);

        return response()->noContent();
    }
}
