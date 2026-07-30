<?php

use App\Models\User;

test('the root url sends a guest to the login screen', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('login'));
});

test('the root url sends an authenticated person to the feed', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('home'));

    $response->assertRedirect(route('feed'));
});
