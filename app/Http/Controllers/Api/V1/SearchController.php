<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController
{
    public function __invoke(Request $request): JsonResponse
    {
        $input = $request->validate(['q' => ['required', 'string', 'max:120']]);
        $pattern = '%'.strtr(mb_strtolower($input['q']), ['!' => '!!', '%' => '!%', '_' => '!_']).'%';
        $user = $request->user();

        $tasks = $user->tasks()->where(function (Builder $query) use ($pattern) {
            $query->whereRaw("LOWER(title) LIKE ? ESCAPE '!'", [$pattern])
                ->orWhereRaw("LOWER(description) LIKE ? ESCAPE '!'", [$pattern]);
        })->limit(10)->get(['uuid', 'title']);

        $projects = $user->projects()->whereRaw("LOWER(name) LIKE ? ESCAPE '!'", [$pattern])
            ->limit(10)->get(['uuid', 'name']);

        return response()->json(['data' => ['tasks' => $tasks, 'projects' => $projects]]);
    }
}
