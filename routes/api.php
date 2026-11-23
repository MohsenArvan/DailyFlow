<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ExportController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ProjectNoteController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\SubtaskController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => 'DailyFlow API',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Guest Auth Routes
Route::prefix('auth')->middleware('guest')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:15,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:15,1');
});

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::prefix('user')->group(function () {
        Route::put('/profile', [UserController::class, 'updateProfile']);
        Route::put('/preferences', [UserController::class, 'updatePreferences']);
        Route::put('/password', [UserController::class, 'updatePassword']);
    });

    Route::get('/search', SearchController::class);
    Route::get('/user/export', ExportController::class);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'read'])->whereUuid('id');

    Route::prefix('dashboard')->group(function () {
        Route::get('/summary', [DashboardController::class, 'summary']);
        Route::get('/velocity', [DashboardController::class, 'velocity']);
        Route::get('/metrics', [DashboardController::class, 'summary']);
        Route::get('/weekly-velocity', [DashboardController::class, 'velocity']);
    });

    // Tasks API
    Route::get('/tasks/counts', [TaskController::class, 'counts']);
    Route::post('/tasks/reorder', [TaskController::class, 'reorder']);
    Route::post('/tasks/{task}/complete', [TaskController::class, 'complete']);
    Route::post('/tasks/{task}/reopen', [TaskController::class, 'reopen']);
    Route::apiResource('tasks', TaskController::class);

    // Subtasks API
    Route::post('/tasks/{task}/subtasks', [SubtaskController::class, 'store']);
    Route::post('/tasks/{task}/subtasks/reorder', [SubtaskController::class, 'reorder']);
    Route::put('/subtasks/{subtask}', [SubtaskController::class, 'update']);
    Route::post('/subtasks/{subtask}/toggle', [SubtaskController::class, 'toggle']);
    Route::delete('/subtasks/{subtask}', [SubtaskController::class, 'destroy']);

    // Projects API
    Route::post('/projects/reorder', [ProjectController::class, 'reorder']);
    Route::post('/projects/{project}/favorite', [ProjectController::class, 'toggleFavorite']);
    Route::apiResource('projects', ProjectController::class);
    Route::prefix('projects/{project}/notes')->controller(ProjectNoteController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::patch('/{noteUuid}', 'update')->whereUuid('noteUuid');
        Route::delete('/{noteUuid}', 'destroy')->whereUuid('noteUuid');
    });
});
