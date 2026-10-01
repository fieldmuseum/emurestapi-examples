<?php

/**
 * Hits a real EMu REST API. Skipped unless php/.env holds working credentials.
 *
 * Run just these with: ./vendor/bin/pest --group=integration
 *
 * EMUAPI_TEST_IRN sets the record to read; it defaults to 1, which may well not
 * exist in your tenant. Point it at a record you know about.
 */

use EMuRestApi\Texpress\Retrieve;
use EMuRestApi\Tokens\Auth;

beforeEach(fn () => resetEmuApi());

test('test that record() returns a response body with results', function () {
    // First, get the auth token
    $auth = new Auth();
    $auth->setToken($_ENV['EMUAPI_USER'], $_ENV['EMUAPI_PASSWORD']);
    $authToken = $auth->token();
    expect($authToken)->not->toBeEmpty();
    expect($authToken)->toContain('Bearer');

    $fieldsToReturn = [
        'id',
        'data.SummaryData',
        'data.CatDepartment',
        'data.CatCatalogSubset',
        'data.DarGenus',
        'data.DarSpecies',
    ];

    $irn = $_ENV['EMUAPI_TEST_IRN'] ?? '1';

    $get = new Retrieve();
    $result = $get->record($authToken, 'ecatalogue', $irn, $fieldsToReturn);

    expect($result['authToken'])->not->toBeEmpty();
    expect($result['data'])->not->toBeEmpty();

    // The token that came back can be used for the next request, which is what
    // `renew` buys you. Here it reads the same record a second time.
    $again = $get->record($result['authToken'], 'ecatalogue', $irn, $fieldsToReturn);
    expect($again['data'])->toBe($result['data']);
})->group('integration')->skip(fn () => missingLiveCredentials(), NO_LIVE_CREDENTIALS);
