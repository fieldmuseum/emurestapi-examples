<?php

test('test that getAuthToken() returns an Authorization bearer token', function () {
    $requestUrl = getAuthToken("barney", "pw");

    expect($requestUrl)->not->toBeEmpty();
});
