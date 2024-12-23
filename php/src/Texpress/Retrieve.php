<?php

/**
 * emurestapi version 3.1.2
 *
 * This file shows how to get an individual record.
 * @link https://help.emu.axiell.com/emurestapi/3.1.2/04-Resources-Texpress.html#retrieve
 *
 */

namespace EMuRestApi\Texpress;

use GuzzleHttp\Client;

class Retrieve
{
    public function singleRecord(string $authToken, string $resource, string $irn, array $fieldsToReturn): array
    {
        if (empty($authToken)) {
            throw new Exception("no auth token provided!");
        }
        if (empty($resource)) {
            throw new Exception("no resource, to retrieve from, provided!");
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

        $endpoint = "/{$tenant}/{$resource}/{$irn}";
        if (!empty($fieldsToReturn)) {
            $fields = implode(",", $fieldsToReturn);
            $endpoint .= "?select={$fields}";
        }
    
        try {
            $response = $client->get($endpoint, [ 'headers' => $headers ]);
            $body = $response->getBody();
            $jsonString = $body->getContents();
            $data = json_decode($jsonString, true);
    
            // Keep token for next request
            $authToken = $response->getHeaderLine('Authorization');
    
            return [ 'data' => $data, 'authToken' => $authToken ];
        } catch (Exception $e) {
            throw new Exception('Error retrieving from the emurestapi: ' . $e->getMessage());
        }
    
        return [];
    }
}
