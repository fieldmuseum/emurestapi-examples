<?php

/**
 * This file shows how to get a JWT for authentication to the EMu REST API.
 * 
 * A key thing to note is that when you do a POST request to get the Bearer token,
 * you'll need to get the token from the "Authorization" response header. Don't look
 * in the response body.
 * 
 * Check out your options for the auth token here, specifically the timeout and renew options.
 * If renew is set to true, then new auth tokens will be generated with each request
 * and you can just pass the new response Authorization header from request to request.
 * @link https://help.emu.axiell.com/emurestapi/3.1.2/04-Resources-Tokens.html#username-password
 * 
 * Guzzle is being used to perform the requests and it's pretty standard in the PHP
 * community so we'll use that for our example.
 * @link https://docs.guzzlephp.org/en/stable/psr7.html
 */

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

/**
 * Gets an authorization token from the emurestapi 
 * @link https://help.emu.axiell.com/emurestapi/3.1.2/04-Resources-Tokens.html
 * 
 * @param string $username
 * @param string $password
 * @param int $timeout
 *   idle/elapsed time in minutes before expiry of the created token
 * @param boolean $renew
 *   whether new tokens should be generated with each request
 * 
 * @return string
 *   Returns the response header Authorization token
 */
function getAuthToken(string $username, string $password, $timeout = 30, $renew = true): string {
    $responseBody = "";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '../.env');
    $dotenv->load();

    $url = $_ENV['EMUAPI_URL'];
    $port = $_ENV['EMUAPI_PORT'];
    $baseUri = "{$url}:{$port}";

    $tenant = $_ENV['EMUAPI_TENANT'];
    $endpoint = "/{$tenant}/tokens";

    $client = new Client([
        'base_uri' => $baseUri,
    ]);

    $headers = [
        'Content-Type' => 'application/json',
        'Prefer' => 'representation=minimal',
    ];

    $data = [
        'username' => $_ENV['EMUAPI_USER'],
        'password' => $_ENV['EMUAPI_PASSWORD'],
        'timeout' => $timeout,
        'renew' => $renew
    ];

    try {
        $response = $client->post($endpoint,
            [
                'headers' => $headers,
                'body' => json_encode($data),
            ]
        );
        $responseBody = $response->getBody();
    } catch (\Exception $e) {
        $errorMsg = 'Error get auth token: ' . $e->getMessage();
        print $errorMsg;
        throw new Exception($errorMsg, 1);
    }

    return $responseBody;
}
