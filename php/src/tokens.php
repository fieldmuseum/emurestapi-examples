<?php

/**
 * This file shows how to get an JWT for authentication to the EMu REST API.
 * 
 * A key thing to note is that when you do a POST request to get the Bearer token,
 * you'll need to get that from the "Authorization" response header. Don't look
 * in the response body.
 * 
 * Guzzle is being used to perform the requests and it's pretty standard in the PHP
 * community so we'll use that for our example.
 * @link https://docs.guzzlephp.org/en/stable/psr7.html
 */

use GuzzleHttp\Psr7\Request;

/**
 * Gets an authorization token from the emurestapi 
 * @link https://help.emu.axiell.com/emurestapi/3.1.2/04-Resources-Tokens.html
 * 
 * @return string
 *   Returns the response header Authorization bearer token
 */
function getAuthToken(): string {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '../.env');
    $dotenv->load();

    $url = $_ENV['EMUAPI_URL'];
    $port = $_ENV['EMUAPI_PORT'];
    $tenant = $_ENV['EMUTENANT'];

    $requestUrl = "{$url}:{$port}/{$tenant}/tokens";

    return $requestUrl;
}
