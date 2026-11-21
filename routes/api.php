<?php

use App\Http\Controllers\Api\V1\AuthController;
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
});
