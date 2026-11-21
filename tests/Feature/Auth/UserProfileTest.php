<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('authenticated user can update profile', function () {
    $user = User::factory()->create([
        'name' => 'نام قدیمی',
        'timezone' => 'UTC',
    ]);

    $response = $this->actingAs($user)->putJson('/api/v1/user/profile', [
        'name' => 'نام جدید',
        'timezone' => 'Asia/Tehran',
    ]);

    $response->assertOk()
        ->assertJson([
            'message' => 'پروفایل با موفقیت بروزرسانی شد.',
            'user' => [
                'name' => 'نام جدید',
                'timezone' => 'Asia/Tehran',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'نام جدید',
        'timezone' => 'Asia/Tehran',
    ]);
});

test('authenticated user can update preferences', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->putJson('/api/v1/user/preferences', [
        'theme' => 'light',
        'date_format' => 'gregorian',
        'daily_goal' => 8,
    ]);

    $response->assertOk()
        ->assertJson([
            'user' => [
                'preferences' => [
                    'theme' => 'light',
                    'date_format' => 'gregorian',
                    'daily_goal' => 8,
                    'start_of_week' => 6,
                ],
            ],
        ]);
});

test('authenticated user can change password with valid current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('OldPassword123!'),
    ]);

    $response = $this->actingAs($user)->putJson('/api/v1/user/password', [
        'current_password' => 'OldPassword123!',
        'password' => 'NewSecretPassword123!',
        'password_confirmation' => 'NewSecretPassword123!',
    ]);

    $response->assertOk()
        ->assertJson([
            'message' => 'کلمه عبور با موفقیت تغییر یافت.',
        ]);

    $user->refresh();
    $this->assertTrue(Hash::check('NewSecretPassword123!', $user->password));
});

test('password update fails with invalid current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('OldPassword123!'),
    ]);

    $response = $this->actingAs($user)->putJson('/api/v1/user/password', [
        'current_password' => 'WrongCurrentPassword!',
        'password' => 'NewSecretPassword123!',
        'password_confirmation' => 'NewSecretPassword123!',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['current_password']);
});

test('wallpaper preferences accept bundled identifiers and reject arbitrary URLs', function () {
    $user = User::factory()->create(['preferences' => ['theme' => 'light']]);
    $this->actingAs($user)->getJson('/api/v1/auth/me')
        ->assertJsonPath('user.preferences.wallpaper', 'lake');

    $this->putJson('/api/v1/user/preferences', ['wallpaper' => 'dunes'])->assertOk()
        ->assertJsonPath('user.preferences.wallpaper', 'dunes')
        ->assertJsonPath('user.preferences.theme', 'light');

    $this->putJson('/api/v1/user/preferences', ['wallpaper' => 'https://example.test/photo.jpg'])
        ->assertUnprocessable()->assertJsonValidationErrors('wallpaper');

    $this->getJson('/api/v1/auth/me')->assertJsonPath('user.preferences.wallpaper', 'dunes');
});
