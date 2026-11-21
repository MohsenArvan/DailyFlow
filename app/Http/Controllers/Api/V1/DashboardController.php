<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardSummaryResource;
use App\Services\Analytics\ProductivityMetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function summary(Request $request, ProductivityMetricsService $metrics): DashboardSummaryResource
    {
        return new DashboardSummaryResource($metrics->summary($request->user()));
    }

    public function velocity(Request $request, ProductivityMetricsService $metrics): JsonResponse
    {
        return response()->json(['data' => $metrics->velocity($request->user())]);
    }
}
