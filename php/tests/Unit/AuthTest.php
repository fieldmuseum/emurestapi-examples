<?php

test('test that getAuthToken() returns an Authorization bearer token', function () {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '../../.env');
    $dotenv->load();

    $authToken = getAuthToken($_ENV['EMUAPI_USER'], $_ENV['EMUAPI_PASSWORD']);

    expect($authToken)->not->toBeEmpty();
    expect($authToken)->toContain("Bearer");
});
