<?php

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

$servicios = new stdClass();

$config = require_once 'conf.php';

$db = $config['db'];
$conn = new PDO("${db['motor']}:host=${db['host']};dbname=${db['dbname']}", $db['username'], $db['passwd'], $db['attr']);
$servicios->db = $conn;

require_once '../vendor/autoload.php'; // por si bajaste por composer el SDK
// require_once __DIR__  . '/PayPal-PHP-SDK/autoload.php'; // por si bajaste directamente el SDK

$paypal = $config['paypal'];
$apiContext = new ApiContext(new OAuthTokenCredential($paypal['clientId'], $paypal['clientSecret']));
$apiContext->setConfig($paypal['config']);
$servicios->paypal = $apiContext;

return $servicios;