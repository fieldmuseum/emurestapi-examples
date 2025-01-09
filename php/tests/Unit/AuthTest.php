<?php

use Dotenv\Dotenv;
use EMuRestApi\Tokens\Auth;

test('test that token() returns an Authorization bearer token', function () {
    $dotenv = Dotenv::createImmutable(__DIR__, '../../.env');
    $dotenv->load();

    $auth = new Auth();
    $auth->setToken($_ENV['EMUAPI_USER'], $_ENV['EMUAPI_PASSWORD']);
    $authToken = $auth->token();

    expect($authToken)->not->toBeEmpty();
    expect($authToken)->toContain("Bearer");
});
