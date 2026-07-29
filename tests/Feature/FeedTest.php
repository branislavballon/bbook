<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('feed'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the feed', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('feed'));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page->component('feed'));
});
