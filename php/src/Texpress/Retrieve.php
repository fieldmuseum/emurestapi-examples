<?php

/**
 * This file shows how to get an individual record.
 * @link https://help.emu.axiell.com/emurestapi/latest/04-Resources-Texpress.html#retrieve
 */

namespace EMuRestApi\Texpress;

use EMuRestApi\Config;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use RuntimeException;

class Retrieve
{
    /**
     * Retrieves a single record from an EMu module.
     *
     * @param string $authToken
     *   An Authorization header value from Auth::token(), or from a previous
     *   response when the token was created with renew enabled
     * @param string $resource
     *   The EMu module to read from, e.g. "ecatalogue"
     * @param string $irn
     *   The record's internal record number
     * @param string[] $fieldsToReturn
     *   Fields to select, e.g. ["id", "data.SummaryData"]. Pass an empty array
     *   for the whole record.
     *
     * @return array{data: mixed, authToken: string}
     *
     * @throws InvalidArgumentException When a required argument is missing
     * @throws RuntimeException When the API cannot be reached or rejects the request
     */
    public function record(string $authToken, string $resource, string $irn, array $fieldsToReturn): array
    {
        if (empty($authToken)) {
            throw new InvalidArgumentException("no auth token provided!");
        }
        if (empty($resource)) {
            throw new InvalidArgumentException("no resource, to retrieve from, provided!");
        }
        if (empty($irn)) {
            throw new InvalidArgumentException("no irn provided!");
        }

        $headers = [
            'Authorization' => $authToken,
            'Prefer' => 'representation=minimal',
        ];

        $endpoint = Config::tenantPath($resource, $irn);
        if (!empty($fieldsToReturn)) {
            // Encode each field name, but leave the separating commas intact.
            $fields = implode(",", array_map('rawurlencode', $fieldsToReturn));
            $endpoint .= "?select={$fields}";
        }

        try {
            $response = Config::client()->get($endpoint, ['headers' => $headers]);
        } catch (GuzzleException $e) {
            throw new RuntimeException('Error retrieving from the emurestapi: ' . $e->getMessage(), 0, $e);
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
