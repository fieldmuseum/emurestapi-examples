<?php

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '.env');
$dotenv->load();

$authToken = getAuthToken($_ENV['EMUAPI_USER'], $_ENV['EMUAPI_PASSWORD']);
$log->debug('Auth token', [$authToken]);

print_r($authToken);
