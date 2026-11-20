<?php

test('health endpoint returns ok status and metadata', function () {
    $response = $this->getJson('/api/v1/health');

    $response->assertOk()
        ->assertJson([
            'status' => 'ok',
            'app' => 'DailyFlow API',
            'version' => '1.0.0',
        ])
        ->assertJsonStructure([
            'status',
            'app',
            'version',
            'timestamp',
        ]);
});

test('spa root endpoint returns ok status', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('DailyFlow');
});
