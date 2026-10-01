<?php

/**
 * PLEASE NOTE: the documentation for Search is unclear. This request must be a POST
 * with the method overridden to a GET, not a GET. See the Override doc below.
 * @link https://help.emu.axiell.com/emurestapi/latest/04-Resources-Texpress.html#search
 * @link https://help.emu.axiell.com/emurestapi/latest/05-Appendices-Override.html
 *
 * Query Syntax can be found in the appendices
 * @link https://help.emu.axiell.com/emurestapi/latest/05-Appendices-Query.html
 */

namespace EMuRestApi\Texpress;

use EMuRestApi\Config;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use RuntimeException;

class Search
{
    /**
     * Searches an EMu module for records.
     *
     * @param string $authToken
     *   An Authorization header value from Auth::token(), or from a previous
     *   response when the token was created with renew enabled
     * @param string $resource
     *   The EMu module to search, e.g. "eparties"
     * @param array<string, mixed> $formData
     *   Form parameters such as filter, sort and limit
     *
     * @return array{data: mixed, authToken: string}
     *
     * @throws InvalidArgumentException When a required argument is missing
     * @throws RuntimeException When the API cannot be reached or rejects the request
     */
    public function resource(string $authToken, string $resource, array $formData): array
    {
        if (empty($authToken)) {
            throw new InvalidArgumentException("no auth token provided!");
        }
        if (empty($resource)) {
            throw new InvalidArgumentException("no resource, to search on, provided!");
        }
        if (empty($formData)) {
            throw new InvalidArgumentException("no form data provided!");
        }

        $headers = [
            'Authorization' => $authToken,
            'Prefer' => 'representation=minimal',
            // A search is a POST that the API treats as a GET. Without this
            // header the request is handled as a write.
            'X-HTTP-Method-Override' => 'GET',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $endpoint = Config::tenantPath($resource);

        try {
            $response = Config::client()->post($endpoint, [
                'headers' => $headers,
                'form_params' => $formData,
            ]);
        } catch (GuzzleException $e) {
            throw new RuntimeException('Error searching the emurestapi: ' . $e->getMessage(), 0, $e);
        }

        $data = json_decode($response->getBody()->getContents(), true);

        // Keep the token for the next request: when the token was created with
        // renew enabled, this header holds a fresh one with an updated expiry.
        $nextToken = $response->getHeaderLine('Authorization');

        return [
            'data' => $data,
            'authToken' => empty($nextToken) ? $authToken : $nextToken,
        ];
    }
}
