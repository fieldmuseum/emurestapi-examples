<?php

use Dotenv\Dotenv;
use EMuRestApi\Tokens\Auth;

test('test that getAuthToken() returns an Authorization bearer token', function () {
    $dotenv = Dotenv::createImmutable(__DIR__, '../../.env');
    $dotenv->load();

    $auth = new Auth();
    $auth->setAuthToken($_ENV['EMUAPI_USER'], $_ENV['EMUAPI_PASSWORD']);
    $authToken = $auth->getAuthToken();

    expect($authToken)->not->toBeEmpty();
    expect($authToken)->toContain("Bearer");
});
