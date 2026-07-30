<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

test('passkeys are not among the registered fortify features', function () {
    expect(config('fortify.features'))->not->toContain(Features::passkeys())
        ->and(Features::enabled(Features::passkeys()))->toBeFalse()
        ->and(Features::canManagePasskeys())->toBeFalse();
});

/**
 * Named rather than pattern-matched: Fortify owns these names, so a rename on
 * its side should fail this test loudly instead of passing it vacuously.
 */
test('fortify registers none of its passkey routes while the feature is off', function (string $name) {
    expect(Route::has($name))->toBeFalse();
})->with([
    'passkey.login-options',
    'passkey.login',
    'passkey.confirm-options',
    'passkey.confirm',
    'passkey.registration-options',
    'passkey.store',
    'passkey.destroy',
]);

test('the passkey endpoints discovery document is gone', function () {
    $this->get('/.well-known/passkey-endpoints')->assertNotFound();

    expect(Route::has('well-known.passkeys'))->toBeFalse();
});
