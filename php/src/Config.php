<?php

/**
 * Shared configuration for the examples: where your EMu REST API lives, and the
 * credentials used to authenticate against it. Every example reads its settings
 * from here so that the example files themselves stay focused on the parts that
 * are specific to the EMu API.
 *
 * Copy `.env.example` to `.env` and fill it in before running anything.
 *
 * Guzzle is being used to perform the requests and it's pretty standard in the PHP
 * community so we'll use that for our examples.
 * @link https://docs.guzzlephp.org/en/stable/psr7.html
 */

namespace EMuRestApi;

use GuzzleHttp\Client;
use RuntimeException;

class Config
{
    /**
     * Seconds to wait for the connection, and for the request overall, before
     * giving up. Without these a request to an unreachable EMu host sits there
     * until cURL's own very long default expires.
     */
    public const CONNECT_TIMEOUT = 5;
    public const REQUEST_TIMEOUT = 15;

    /**
     * A Guzzle handler to use instead of real HTTP.
     *
     * Only the tests set this, so that the examples can be exercised without a
     * live EMu installation. See php/tests/Unit for how it is used.
     *
     * @var callable|null
     */
    private static $testHandler = null;

    /**
     * Swaps the HTTP layer out for a test double; pass null to restore it.
     */
    public static function useTestHandler(?callable $handler): void
    {
        self::$testHandler = $handler;
    }

    /**
     * Reads a required environment variable, failing loudly when it is missing.
     *
     * @throws RuntimeException
     */
    public static function required(string $name): string
    {
        $value = $_ENV[$name] ?? getenv($name) ?: '';

        if (empty($value)) {
            throw new RuntimeException("missing {$name}! check your env file/variables");
        }

        return (string) $value;
    }

    /**
     * The base URI of the API, with the port appended only when one is configured.
     *
     * EMu installations behind a reverse proxy are often served on the default
     * port, in which case EMUAPI_PORT is left empty.
     */
    public static function baseUri(): string
    {
        $baseUri = self::required('EMUAPI_URL');
        $port = $_ENV['EMUAPI_PORT'] ?? '';

        if (!empty($port)) {
            $baseUri .= ":{$port}";
        }

        return $baseUri;
    }

    /**
     * Tenant is the EMu machine name for your institution.
     */
    public static function tenant(): string
    {
        return self::required('EMUAPI_TENANT');
    }

    /**
     * Builds a path under your tenant, e.g. tenantPath('ecatalogue', '1')
     * => "/mymuseum/ecatalogue/1"
     */
    public static function tenantPath(string ...$segments): string
    {
        $path = array_map('rawurlencode', [self::tenant(), ...$segments]);

        return '/' . implode('/', $path);
    }

    /**
     * A Guzzle client pointed at your API, with sane timeouts.
     */
    public static function client(): Client
    {
        $options = [
            'base_uri' => self::baseUri(),
            'connect_timeout' => self::CONNECT_TIMEOUT,
            'timeout' => self::REQUEST_TIMEOUT,
        ];

        if (self::$testHandler !== null) {
            $options['handler'] = self::$testHandler;
        }

        return new Client($options);
    }
}
