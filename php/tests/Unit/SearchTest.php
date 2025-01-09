<?php

use Dotenv\Dotenv;
use EMuRestApi\Tokens\Auth;
use EMuRestApi\Texpress\Search;

test('test that searchResource() returns a response body with results', function () {
    $dotenv = Dotenv::createImmutable(__DIR__, '../../.env');
    $dotenv->load();

    // First, get the auth token
    $auth = new Auth();
    $auth->setAuthToken($_ENV['EMUAPI_USER'], $_ENV['EMUAPI_PASSWORD']);
    $authToken = $auth->getAuthToken();
    expect($authToken)->not->toBeEmpty();
    expect($authToken)->toContain("Bearer");

    // Perform the search
    $formData = [
        'filter' => '{"AND":[{"data.NamLast":{"exact":{"value": "Smith"}}}]}',
        'sort' => '[{"data.NamFirst":{"order":"asc"}}]',
        'limit' => 5,
    ];

    $search = new Search();

    $results = $search->resource($authToken, "eparties", $formData);
    expect($results['authToken'])->not->toBeEmpty();
    expect($results['data']['hits'])->toBeGreaterThan(0);
});
