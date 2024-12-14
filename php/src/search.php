<?php

/**
 * emurestapi version 3.1.2
 *
 * This file shows how to search an EMu module. As noted in the docs link below,
 * one of the key differences between a Search and a Retrieves is that a Search
 * lacks an {id} to retrieve in the request URL.
 *
 * emurestapi texpress search docs
 * @link https://docs.guzzlephp.org/en/stable/psr7.html
 */

use GuzzleHttp\Client;

function searchResource(string $authToken, string $resource, array $searchOptions) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '../.env');
    $dotenv->load();

    if (empty($authToken)) {
        throw new Exception("no auth token provided!");
    }
    if (empty($resource)) {
        throw new Exception("no resource, to search on, provided!");
    }
    if (empty($searchOptions)) {
        throw new Exception("no search options provided!");
    }

    // TODO: start here

    return "";
}
