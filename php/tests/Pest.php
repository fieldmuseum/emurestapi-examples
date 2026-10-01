<?php

use Dotenv\Dotenv;
use EMuRestApi\Config;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Middleware;

/*
|--------------------------------------------------------------------------
| Bootstrap
|--------------------------------------------------------------------------
|
| Load php/.env if it exists. Only the integration tests need it; the unit
| tests mock the HTTP layer and set their own environment values, so they run
| on a fresh clone with no credentials at all.
|
*/

if (file_exists(__DIR__ . '/../.env')) {
    Dotenv::createImmutable(__DIR__ . '/..')->load();
}

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Settings the examples read, pointing at a host that does not exist. Used by
 * the unit tests, which never send a real request.
 */
function fakeEmuEnv(): void
{
    $_ENV['EMUAPI_URL'] = 'http://emu.example.test';
    $_ENV['EMUAPI_PORT'] = '8080';
    $_ENV['EMUAPI_TENANT'] = 'mymuseum';
}

/**
 * Points the examples at canned responses instead of a live EMu.
 *
 * @param \GuzzleHttp\Psr7\Response[] $responses Returned in order, one per request
 *
 * @return ArrayObject A log of the requests the example sent, so a test can
 *   assert on the method, URL, headers and body that were actually used.
 */
function mockEmuApi(array $responses): ArrayObject
{
    fakeEmuEnv();

    $sent = new ArrayObject();
    $stack = HandlerStack::create(new MockHandler($responses));
    $stack->push(Middleware::history($sent));
    Config::useTestHandler($stack);

    return $sent;
}

/**
 * Restores the real HTTP layer. Call this from afterEach() in a unit test file.
 */
function resetEmuApi(): void
{
    Config::useTestHandler(null);
}

/**
 * Whether live credentials are configured. The integration tests skip
 * themselves when they are not; see php/.env.example.
 */
function missingLiveCredentials(): bool
{
    foreach (['EMUAPI_URL', 'EMUAPI_TENANT', 'EMUAPI_USER', 'EMUAPI_PASSWORD'] as $name) {
        if (empty($_ENV[$name])) {
            return true;
        }
    }

    return false;
}

/** The message shown when an integration test is skipped. */
const NO_LIVE_CREDENTIALS = 'live EMu credentials not configured; copy .env.example to .env to run this';
