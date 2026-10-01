<?php

/**
 * Hits a real EMu REST API. Skipped unless php/.env holds working credentials.
 *
 * Run just these with: ./vendor/bin/pest --group=integration
 */

use EMuRestApi\Tokens\Auth;

beforeEach(fn () => resetEmuApi());

test('test that token() returns an Authorization bearer token', function () {
    $auth = new Auth();
    $auth->setToken($_ENV['EMUAPI_USER'], $_ENV['EMUAPI_PASSWORD']);
    $authToken = $auth->token();

    expect($authToken)->not->toBeEmpty();
    expect($authToken)->toContain('Bearer');
})->group('integration')->skip(fn () => missingLiveCredentials(), NO_LIVE_CREDENTIALS);
