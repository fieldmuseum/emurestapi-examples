<?php

use Dotenv\Dotenv;
use EMuRestApi\Tokens\Auth;
use EMuRestApi\Texpress\Retrieve;

test('test that singleRecord() returns a response body with results', function () {
    $dotenv = Dotenv::createImmutable(__DIR__, '../../.env');
    $dotenv->load();

    // First, get the auth token
    $auth = new Auth();
    $auth->setAuthToken($_ENV['EMUAPI_USER'], $_ENV['EMUAPI_PASSWORD']);
    $authToken = $auth->getAuthToken();
    expect($authToken)->not->toBeEmpty();
    expect($authToken)->toContain("Bearer");

    $fieldsToReturn = [
        "id",
        "data.NamFirst",
        "data.NamLast",
    ];

    $get = new Retrieve();
    $result = $get->singleRecord($authToken, "eparties", "1", $fieldsToReturn);

    print_r($result);

    expect($result['authToken'])->not->toBeEmpty();
    expect($result['data'])->not->toBeEmpty();
});
