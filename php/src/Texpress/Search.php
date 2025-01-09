<?php

/**
 * emurestapi version 3.1.3
 *
 * As of Jan 2025
 * PLEASE NOTE!! The documentation for Search is unclear or wrong. You cannot do a GET
 * request. You must perform a POST request overidden to a GET request, with a x-www-form-encoded body.
 * This function performs a X-HTTP-Method-Override as a GET request.
 * See below for implementation details.
 *
 * This file shows how to search an EMu module. As noted in the docs link below,
 * one of the key differences between a Search and a Retrieves is that a Search
 * lacks an {id} to retrieve in the request URL.
 * @link https://help.emu.axiell.com/emurestapi/3.1.3/04-Resources-Texpress.html#search
 *
 * Query Syntax can be found in the appendices
 * @link https://help.emu.axiell.com/emurestapi/3.1.3/05-Appendices-Query.html
 */

namespace EMuRestApi\Texpress;

use GuzzleHttp\Client;

class Search
{
    public function searchResource(string $authToken, string $resource, array $formData): array
    {
        if (empty($authToken)) {
            throw new Exception("no auth token provided!");
        }
        if (empty($resource)) {
            throw new Exception("no resource, to search on, provided!");
        }
        if (empty($formData)) {
            throw new Exception("no form data provided!");
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
            'Authorization' => $authToken,
            'Prefer' => 'representation=minimal',
            'X-HTTP-Method-Override' => 'GET',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $endpoint = "/{$tenant}/{$resource}";

        try {
            $response = $client->post($endpoint, [
                'headers' => $headers,
                'form_params' => $formData
            ]);
            $body = $response->getBody();
            $jsonString = $body->getContents();
            $data = json_decode($jsonString, true);

            // Keep token for next request
            $authToken = $response->getHeaderLine('Authorization');

            return [ 'data' => $data, 'authToken' => $authToken ];
        } catch (Exception $e) {
            throw new Exception('Error searching the emurestapi: ' . $e->getMessage());
        }

        return [];
    }
}
