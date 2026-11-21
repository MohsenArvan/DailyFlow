<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(ThrottleRequests::class);
});

test('user can login with correct credentials', function () {
    $user = User::factory()->create([
        'email' => 'ali@example.com',
        'password' => bcrypt('Secret1234!'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'ali@example.com',
        'password' => 'Secret1234!',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'name',
                'email',
                'timezone',
                'preferences',
            ],
        ])
        ->assertJson([
            'user' => [
                'id' => $user->id,
                'email' => 'ali@example.com',
            ],
        ]);

    $this->assertAuthenticatedAs($user);
});

test('user cannot login with incorrect password', function () {
    User::factory()->create([
        'email' => 'ali@example.com',
        'password' => bcrypt('CorrectPassword123!'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'ali@example.com',
        'password' => 'WrongPassword123!',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);

    $this->assertGuest();
});

test('authenticated user can retrieve their profile via /me', function () {
    $user = User::factory()->create([
        'name' => 'رضا مرادی',
        'email' => 'reza@example.com',
        'timezone' => 'Asia/Tehran',
    ]);

    $response = $this->actingAs($user)->getJson('/api/v1/auth/me');

    $response->assertOk()
        ->assertJson([
            'user' => [
                'id' => $user->id,
                'name' => 'رضا مرادی',
                'email' => 'reza@example.com',
                'timezone' => 'Asia/Tehran',
            ],
        ]);
});

test('unauthenticated user cannot access /me', function () {
    $response = $this->getJson('/api/v1/auth/me');

    $response->assertUnauthorized();
});

test('authenticated user can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/v1/auth/logout');

    $response->assertOk()
        ->assertJson([
            'message' => 'خروج با موفقیت انجام شد.',
        ]);
});
