<?php

error_reporting(E_ALL);

date_default_timezone_set('UTC');

require 'vendor/autoload.php';

$configPath = __DIR__ . '/etc';
$configData = include $configPath . '/config.php';
if (file_exists($configPath . '/config.local.php')) {
    $localConfig = include $configPath . '/config.local.php';
    $configData = array_replace_recursive($configData, $localConfig);
}


$container = new \App\Container($configData);

/** @var \App\App $app */
$app = $container['app'];

$response = $app->getResponse();

echo $response;