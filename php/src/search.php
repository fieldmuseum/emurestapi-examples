<?php

/**
 * emurestapi version 3.1.2
 *
 * This file shows how to search an EMu module. As noted in the docs link below,
 * one of the key differences between a Search and a Retrieves is that a Search
 * lacks an {id} to retrieve in the request URL.
 * @link https://help.emu.axiell.com/emurestapi/3.1.2/04-Resources-Texpress.html#search
 *
 * emurestapi texpress search docs
 * @link https://docs.guzzlephp.org/en/stable/psr7.html
 */

namespace EMuRestApi;

use GuzzleHttp\Client;

class Search
{
    public function searchResource(string $authToken, string $resource, string $searchOptions): array
    {    
        if (empty($authToken)) {
            throw new Exception("no auth token provided!");
        }
        if (empty($resource)) {
            throw new Exception("no resource, to search on, provided!");
        }
        if (empty($searchOptions)) {
            throw new Exception("no search options provided!");
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
        ];
    
        $encodedOptions = urlencode($searchOptions);
    
        $endpoint = "/{$tenant}/{$resource}?{$encodedOptions}";
    
        try {
            $response = $client->get($endpoint, [ 'headers' => $headers ]);
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
