<?php

/**
 * This file shows how to get a JWT for authentication to the EMu REST API.
 *
 * A key thing to note is that when you do a POST request to get the Bearer token,
 * you'll need to get the token from the "Authorization" response header. Don't look
 * in the response body.
 *
 * Check out your options for the auth token here, specifically the timeout and renew
 * options. If renew is set to true, then new auth tokens will be generated (with
 * updated expiry time) with each request and you can just pass the new Authorization
 * header from request to request. See Retrieve.php and Search.php for how that is done.
 * @link https://help.emu.axiell.com/emurestapi/latest/04-Resources-Tokens.html#username-password
 */

namespace EMuRestApi\Tokens;

use EMuRestApi\Config;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use RuntimeException;

class Auth
{
    /**
     * The Authorization Bearer token
     */
    protected ?string $authToken = null;

    /**
     * Gets the Authorization Bearer token, or null when setToken() has not run yet.
     */
    public function token(): ?string
    {
        return $this->authToken;
    }

    /**
     * Sets an authorization token from the emurestapi
     * @link https://help.emu.axiell.com/emurestapi/latest/04-Resources-Tokens.html
     *
     * @param string $username
     * @param string $password
     * @param int $timeout
     *   Idle/elapsed time in minutes before expiry of the created token
     * @param bool $renew
     *   Whether new tokens should be generated with each request
     *
     * @throws InvalidArgumentException When a username or password is missing
     * @throws RuntimeException When the API cannot be reached or rejects the request
     */
    public function setToken(string $username, string $password, int $timeout = 30, bool $renew = true): void
    {
        if (empty($username)) {
            throw new InvalidArgumentException("no username provided!");
        }
        if (empty($password)) {
            throw new InvalidArgumentException("no password provided!");
        }

        $headers = [
            'Content-Type' => 'application/json',
            'Prefer' => 'representation=minimal',
        ];

        $bodyData = [
            'username' => $username,
            'password' => $password,
            'timeout' => $timeout,
            'renew' => $renew,
        ];

        $endpoint = Config::tenantPath('tokens');

        try {
            $response = Config::client()->post($endpoint, [
                'headers' => $headers,
                'body' => json_encode($bodyData),
            ]);
        } catch (GuzzleException $e) {
            throw new RuntimeException('Error getting emurestapi auth token: ' . $e->getMessage(), 0, $e);
        }

        // The token is in the response header, not the body.
        $authToken = $response->getHeaderLine('Authorization');
        if (empty($authToken)) {
            throw new RuntimeException('Authorization header missing from the token response!');
        }

        $this->authToken = $authToken;
    }
}
