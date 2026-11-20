<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(ThrottleRequests::class);
});

test('new user can register with valid credentials', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'سارا احمدی',
        'email' => 'sara@example.com',
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
        'timezone' => 'Asia/Tehran',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'name',
                'email',
                'timezone',
                'preferences' => [
                    'theme',
                    'date_format',
                    'start_of_week',
                    'daily_goal',
                ],
                'created_at',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'sara@example.com',
        'name' => 'سارا احمدی',
        'timezone' => 'Asia/Tehran',
    ]);

    $this->assertAuthenticated();
});

test('registration fails when required fields are missing', function () {
    $response = $this->postJson('/api/v1/auth/register', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

test('registration fails with invalid email', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'کاربر تستی',
        'email' => 'not-an-email',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('registration fails when email is already taken', function () {
    User::factory()->create([
        'email' => 'duplicate@example.com',
    ]);

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'کاربر دوم',
        'email' => 'duplicate@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('registration fails when password confirmation does not match', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'کاربر تستی',
        'email' => 'user@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'DifferentPassword123!',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

test('registration defaults to UTC timezone when none provided', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'کاربر پیش‌فرض',
        'email' => 'default@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('users', [
        'email' => 'default@example.com',
        'timezone' => 'UTC',
    ]);
});
