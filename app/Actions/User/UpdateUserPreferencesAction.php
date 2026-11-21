<?php

namespace App\Actions\User;

use App\Models\User;

class UpdateUserPreferencesAction
{
    /**
     * Update user preferences JSON.
     *
     * @param  array<string, mixed>  $newPreferences
     */
    public function execute(User $user, array $newPreferences): User
    {
        $current = $user->preferences;
        $merged = array_merge($current, $newPreferences);

        $user->update([
            'preferences' => $merged,
        ]);

        return $user->fresh();
    }
}
