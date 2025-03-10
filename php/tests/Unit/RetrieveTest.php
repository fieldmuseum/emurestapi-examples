<?php

use Dotenv\Dotenv;
use EMuRestApi\Tokens\Auth;
use EMuRestApi\Texpress\Retrieve;

test('test that record() returns a response body with results', function () {
    $dotenv = Dotenv::createImmutable(__DIR__, '../../.env');
    $dotenv->load();

    // First, get the auth token
    $auth = new Auth();
    $auth->setToken($_ENV['EMUAPI_USER'], $_ENV['EMUAPI_PASSWORD']);
    $authToken = $auth->token();
    expect($authToken)->not->toBeEmpty();
    expect($authToken)->toContain("Bearer");

    $fieldsToReturn = [
        "id",
        "data.SummaryData",
        "data.CatDepartment",
        "data.CatCatalogSubset",
        "data.DarGenus",
        "data.DarSpecies",
    ];

    $get = new Retrieve();
    $result = $get->record($authToken, "ecatalogue", "1", $fieldsToReturn);

    expect($result['authToken'])->not->toBeEmpty();
    expect($result['data'])->not->toBeEmpty();
});
