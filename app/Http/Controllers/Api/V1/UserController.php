<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\User\UpdateUserPasswordAction;
use App\Actions\User\UpdateUserPreferencesAction;
use App\Actions\User\UpdateUserProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UpdatePreferencesRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(UpdateProfileRequest $request, UpdateUserProfileAction $action): JsonResponse
    {
        $updated = $action->execute($request->user(), $request->validated());

        return response()->json([
            'message' => 'پروفایل با موفقیت بروزرسانی شد.',
            'user' => new UserResource($updated),
        ]);
    }

    /**
     * Update the authenticated user's preferences.
     */
    public function updatePreferences(UpdatePreferencesRequest $request, UpdateUserPreferencesAction $action): JsonResponse
    {
        $updated = $action->execute($request->user(), $request->validated());

        return response()->json([
            'message' => 'تنظیمات با موفقیت ذخیره شد.',
            'user' => new UserResource($updated),
        ]);
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(UpdatePasswordRequest $request, UpdateUserPasswordAction $action): JsonResponse
    {
        $action->execute($request->user(), $request->validated('password'));

        return response()->json([
            'message' => 'کلمه عبور با موفقیت تغییر یافت.',
        ]);
    }
}
