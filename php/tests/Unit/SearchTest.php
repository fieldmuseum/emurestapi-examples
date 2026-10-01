<?php

/**
 * Usage example for EMuRestApi\Texpress\Search, exercised against a mocked API
 * so that it runs without a live EMu installation. The equivalent test against
 * a real tenant is in tests/Integration/SearchTest.php.
 */

use EMuRestApi\Texpress\Search;
use GuzzleHttp\Psr7\Response;

afterEach(fn () => resetEmuApi());

test('resource() returns a response body with results', function () {
    $results = [
        'hits' => 2,
        'matches' => [
            ['id' => '1', 'version' => 1, 'data' => ['irn' => ['id' => '1'], 'NamFullName' => 'Alice Smith']],
            ['id' => '2', 'version' => 1, 'data' => ['irn' => ['id' => '2'], 'NamFullName' => 'Bob Smith']],
        ],
    ];

    $sent = mockEmuApi([
        new Response(200, ['Authorization' => 'Bearer refreshed'], json_encode($results)),
    ]);

    $formData = [
        'filter' => '{"AND":[{"data.NamLast":{"exact":{"value": "Smith"}}}]}',
        'sort' => '[{"data.NamFirst":{"order":"asc"}}]',
        'limit' => 5,
    ];

    $search = new Search();
    $result = $search->resource('Bearer original', 'eparties', $formData);

    expect($result['data']['hits'])->toBe(2);
    expect($result['data']['matches'][0]['data']['NamFullName'])->toBe('Alice Smith');

    // The refreshed token from the response is handed back for the next request.
    expect($result['authToken'])->toBe('Bearer refreshed');

    // A search is a POST that the API is told to treat as a GET. This is the
    // part the official documentation does not make clear.
    $request = $sent[0]['request'];
    expect($request->getMethod())->toBe('POST');
    expect($request->getHeaderLine('X-HTTP-Method-Override'))->toBe('GET');
    expect($request->getHeaderLine('Content-Type'))->toBe('application/x-www-form-urlencoded');
    expect($request->getUri()->getPath())->toBe('/mymuseum/eparties');

    parse_str((string) $request->getBody(), $body);
    expect($body)->toBe([
        'filter' => $formData['filter'],
        'sort' => $formData['sort'],
        'limit' => '5',
    ]);
});

test('resource() rejects missing arguments', function () {
    fakeEmuEnv();

    expect(fn () => (new Search())->resource('', 'eparties', ['limit' => 1]))
        ->toThrow(InvalidArgumentException::class, 'no auth token provided!');
    expect(fn () => (new Search())->resource('Bearer abc', '', ['limit' => 1]))
        ->toThrow(InvalidArgumentException::class, 'no resource, to search on, provided!');
    expect(fn () => (new Search())->resource('Bearer abc', 'eparties', []))
        ->toThrow(InvalidArgumentException::class, 'no form data provided!');
});

test('resource() reports an API error rather than swallowing it', function () {
    mockEmuApi([new Response(400, [], '{"error":"bad query"}')]);

    expect(fn () => (new Search())->resource('Bearer abc', 'eparties', ['filter' => 'nonsense']))
        ->toThrow(RuntimeException::class, 'Error searching the emurestapi');
});
