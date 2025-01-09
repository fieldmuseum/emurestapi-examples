<?php

/**
 * emurestapi version 3.1.3
 *
 * This file shows how to get a JWT for authentication to the EMu REST API.
 * 
 * A key thing to note is that when you do a POST request to get the Bearer token,
 * you'll need to get the token from the "Authorization" response header. Don't look
 * in the response body.
 * 
 * Check out your options for the auth token here, specifically the timeout and renew options.
 * If renew is set to true, then new auth tokens will be generated (with updated expiry time)
 * with each request and you can just pass the new Authorization header from request to request.
 * @link https://help.emu.axiell.com/emurestapi/3.1.3/04-Resources-Tokens.html#username-password
 * 
 * Guzzle is being used to perform the requests and it's pretty standard in the PHP
 * community so we'll use that for our example.
 * @link https://docs.guzzlephp.org/en/stable/psr7.html
 */

namespace EMuRestApi\Tokens;

use GuzzleHttp\Client;

class Auth
{
    /**
     * The Authorization Bearer token
     *
     * @var string
     */
    protected $authToken;

    /**
     * Gets the Authorization Bearer token
     * 
     * @return string
     */
    public function getAuthToken()
    {
        return $this->authToken;
    }

    /**
     * Sets an authorization token from the emurestapi 
     * @link https://help.emu.axiell.com/emurestapi/3.1.2/04-Resources-Tokens.html
     * 
     * @param string $username
     * @param string $password
     * @param int $timeout
     *   Idle/elapsed time in minutes before expiry of the created token
     * @param boolean $renew
     *   Whether new tokens should be generated with each request
     * 
     * @return string
     *   Returns the response header Authorization token
     */
    public function setAuthToken(string $username, string $password, $timeout = 30, $renew = true)
    {
        if (empty($username)) {
            throw new Exception("no username provided!");
        }
        if (empty($password)) {
            throw new Exception("no password provided!");
        }

        $url = $_ENV['EMUAPI_URL'];
        $port = $_ENV['EMUAPI_PORT'];
        $baseUri = $url;

        if (!empty($port)) {
            $baseUri .= ":{$port}";
        }

        // Tenant is the EMu machine name for your institution
        $tenant = $_ENV['EMUAPI_TENANT'];
        if (empty($tenant)) {
            throw new Exception("missing tenant! check your env file/variables");
        }

        $client = new Client([
            'base_uri' => $baseUri,
        ]);

        $headers = [
            'Content-Type' => 'application/json',
            'Prefer' => 'representation=minimal',
        ];

        $bodyData = [
            'username' => $username,
            'password' => $password,
            'timeout' => $timeout,
            'renew' => $renew
        ];

        $endpoint = "/{$tenant}/tokens";

        try {
            $response = $client->post($endpoint,
                [
                    'headers' => $headers,
                    'body' => json_encode($bodyData),
                ]
            );
            $authToken = $response->getHeaderLine('Authorization');
            $this->authToken = $authToken;
        } catch (Exception $e) {
            throw new Exception('Error getting emurestapi auth token: ' . $e->getMessage());
        }
    }
}
