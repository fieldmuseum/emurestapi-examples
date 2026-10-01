<?php

/**
 * Usage example for EMuRestApi\Texpress\Retrieve, exercised against a mocked
 * API so that it runs without a live EMu installation. The equivalent test
 * against a real tenant is in tests/Integration/RetrieveTest.php.
 */

use EMuRestApi\Texpress\Retrieve;
use GuzzleHttp\Psr7\Response;

afterEach(fn () => resetEmuApi());

test('record() returns a response body with results', function () {
    $record = [
        'id' => '1',
        'version' => 1,
        'data' => [
            'irn' => ['id' => '1'],
            'SummaryData' => 'Aphelocoma californica',
            'DarGenus' => 'Aphelocoma',
            'DarSpecies' => 'californica',
        ],
    ];

    $sent = mockEmuApi([
        new Response(200, ['Authorization' => 'Bearer refreshed'], json_encode($record)),
    ]);

    $fieldsToReturn = [
        'id',
        'data.SummaryData',
        'data.CatDepartment',
        'data.CatCatalogSubset',
        'data.DarGenus',
        'data.DarSpecies',
    ];

    $get = new Retrieve();
    $result = $get->record('Bearer original', 'ecatalogue', '1', $fieldsToReturn);

    expect($result['data'])->toBe($record);
    expect($result['data']['data']['DarGenus'])->toBe('Aphelocoma');

    // The refreshed token from the response is handed back for the next request.
    expect($result['authToken'])->toBe('Bearer refreshed');

    // Check the request the example actually sent.
    $request = $sent[0]['request'];
    expect($request->getMethod())->toBe('GET');
    expect($request->getHeaderLine('Authorization'))->toBe('Bearer original');
    expect($request->getUri()->getPath())->toBe('/mymuseum/ecatalogue/1');
    expect(urldecode($request->getUri()->getQuery()))->toBe('select=' . implode(',', $fieldsToReturn));
});

test('record() asks for the whole record when no fields are given', function () {
    $sent = mockEmuApi([new Response(200, [], '{"id":"1"}')]);

    (new Retrieve())->record('Bearer abc', 'ecatalogue', '1', []);

    expect($sent[0]['request']->getUri()->getQuery())->toBe('');
});

test('record() keeps the current token when the response does not refresh it', function () {
    mockEmuApi([new Response(200, [], '{"id":"1"}')]);

    $result = (new Retrieve())->record('Bearer original', 'ecatalogue', '1', []);

    expect($result['authToken'])->toBe('Bearer original');
});

test('record() rejects missing arguments', function () {
    fakeEmuEnv();

    expect(fn () => (new Retrieve())->record('', 'ecatalogue', '1', []))
        ->toThrow(InvalidArgumentException::class, 'no auth token provided!');
    expect(fn () => (new Retrieve())->record('Bearer abc', '', '1', []))
        ->toThrow(InvalidArgumentException::class, 'no resource, to retrieve from, provided!');
    expect(fn () => (new Retrieve())->record('Bearer abc', 'ecatalogue', '', []))
        ->toThrow(InvalidArgumentException::class, 'no irn provided!');
});

test('record() reports an API error rather than swallowing it', function () {
    mockEmuApi([new Response(404, [], '{"error":"not found"}')]);

    expect(fn () => (new Retrieve())->record('Bearer abc', 'ecatalogue', '999999999', []))
        ->toThrow(RuntimeException::class, 'Error retrieving from the emurestapi');
});
