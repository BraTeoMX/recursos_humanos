<?php

test('returns a successful response', function () {
    // Esta línea simula un usuario logueado antes de entrar
    $response = $this->actingAs(\App\Models\User::factory()->create())
                     ->get('/'); 

    $response->assertOk();
});