<?php

namespace App\Actions\User;

use App\Models\User;

class UpdateUserProfileAction
{
    /**
     * Update user profile information.
     *
     * @param  array{name: string, timezone: string}  $data
     */
    public function execute(User $user, array $data): User
    {
        $user->update([
            'name' => $data['name'],
            'timezone' => $data['timezone'],
        ]);

        return $user->fresh();
    }
}
