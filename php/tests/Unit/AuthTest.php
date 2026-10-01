<?php

/**
 * Usage example for EMuRestApi\Tokens\Auth, exercised against a mocked API so
 * that it runs without a live EMu installation. The equivalent test against a
 * real tenant is in tests/Integration/AuthTest.php.
 */

use EMuRestApi\Tokens\Auth;
use GuzzleHttp\Psr7\Response;

afterEach(fn () => resetEmuApi());

test('setToken() reads the Bearer token from the Authorization response header', function () {
    // The token comes back in a header, not in the body.
    $sent = mockEmuApi([
        new Response(200, ['Authorization' => 'Bearer header.payload.signature']),
    ]);

    $auth = new Auth();
    $auth->setToken('username', 'password');

    expect($auth->token())->toBe('Bearer header.payload.signature');

    // Check the request the example actually sent.
    $request = $sent[0]['request'];
    expect($request->getMethod())->toBe('POST');
    expect((string) $request->getUri())->toBe('http://emu.example.test:8080/mymuseum/tokens');
    expect($request->getHeaderLine('Content-Type'))->toBe('application/json');
    expect(json_decode((string) $request->getBody(), true))->toBe([
        'username' => 'username',
        'password' => 'password',
        'timeout' => 30,
        'renew' => true,
    ]);
});

test('setToken() passes the timeout and renew options through', function () {
    $sent = mockEmuApi([new Response(200, ['Authorization' => 'Bearer abc'])]);

    (new Auth())->setToken('username', 'password', timeout: 5, renew: false);

    expect(json_decode((string) $sent[0]['request']->getBody(), true))
        ->toMatchArray(['timeout' => 5, 'renew' => false]);
});

test('token() is null until a token has been fetched', function () {
    expect((new Auth())->token())->toBeNull();
});

test('setToken() rejects missing credentials', function () {
    fakeEmuEnv();

    expect(fn () => (new Auth())->setToken('', 'password'))
        ->toThrow(InvalidArgumentException::class, 'no username provided!');
    expect(fn () => (new Auth())->setToken('username', ''))
        ->toThrow(InvalidArgumentException::class, 'no password provided!');
});

test('setToken() complains when the tenant is not configured', function () {
    fakeEmuEnv();
    $_ENV['EMUAPI_TENANT'] = '';

    expect(fn () => (new Auth())->setToken('username', 'password'))
        ->toThrow(RuntimeException::class, 'missing EMUAPI_TENANT!');
});

test('setToken() reports an API error rather than swallowing it', function () {
    mockEmuApi([new Response(401, [], '{"error":"invalid credentials"}')]);

    expect(fn () => (new Auth())->setToken('username', 'wrong-password'))
        ->toThrow(RuntimeException::class, 'Error getting emurestapi auth token');
});

test('setToken() complains when the response has no Authorization header', function () {
    mockEmuApi([new Response(200)]);

    expect(fn () => (new Auth())->setToken('username', 'password'))
        ->toThrow(RuntimeException::class, 'Authorization header missing');
});
