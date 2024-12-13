<?php

test('test that getAuthToken() returns an Authorization bearer token', function () {
    $requestUrl = getAuthToken();
    print_r($requestUrl);

    expect($requestUrl)->not->toBeEmpty();
});
