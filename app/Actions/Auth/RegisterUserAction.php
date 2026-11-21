<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    /**
     * Register a new user in the system.
     *
     * @param  array{name: string, email: string, password: string, timezone?: string|null}  $data
     */
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => strtolower(trim($data['email'])),
                'password' => Hash::make($data['password']),
                'timezone' => $data['timezone'] ?? 'UTC',
                'preferences' => User::DEFAULT_PREFERENCES,
            ]);

            event(new Registered($user));

            return $user;
        });
    }
}
