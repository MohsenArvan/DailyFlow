<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController
{
    public function unread(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->unreadNotifications()
            ->where('type', 'task-reminder')->limit(50)->get(['id', 'data', 'created_at'])]);
    }

    public function read(Request $request, string $id): JsonResponse
    {
        $request->user()->notifications()->findOrFail($id)->markAsRead();

        return response()->json([], 204);
    }
}
