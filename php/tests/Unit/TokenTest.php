<?php

test('test that getAuthToken() returns an Authorization bearer token', function () {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '../../.env');
    $dotenv->load();

    $respBody = getAuthToken($_ENV['EMUAPI_USER'], $_ENV['EMUAPI_PASSWORD']);
    print_r($respBody);

    expect($respBody)->not->toBeEmpty();
});
