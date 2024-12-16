<?php

use Dotenv\Dotenv;
use EMuRestApi\Auth;
use EMuRestApi\Search;

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
    $filter = <<<TEXT
        {
                "OR": [
                { "data.NamLast": { "contains": { "value": "Smith" } } },
                { "data.NamLast": { "contains": { "value": "Jones" } } }
            ]
        }
        TEXT;
    $sort = <<<TEXT
        [
            { "data.NamFirst": { "order": "asc" } },
            { "data.NamLast": { "order": "desc" } }
        ]
        TEXT;

    $searchOptions = "filter={$filter}";

    $search = new Search();

    $results = $search->searchResource($authToken, "eparties", $searchOptions);

    expect($results['authToken'])->not->toBeEmpty();
    expect($results['data'])->not->toBeEmpty();
});
