<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => 'DailyFlow API',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
    ]);
});
