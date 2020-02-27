<?php

$protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';
$servidor = $_SERVER['SERVER_NAME'];
define('BASE_URI', "${protocolo}://${servidor}/paypal-prueba");
unset($protocolo, $servidor);

return [
	'db' => [
		'motor' => 'mysql',
		'host' => '',
		'dbname' => '',
		'username' => '',
		'passwd' => '',
		'attr' => [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		]
	],
	'paypal' => [
		'clientId' => '',
		'clientSecret' => '',
		'config' => [
			'mode' => 'sandbox',
			'log.LogEnabled' => true,
			'log.FileName' => '../logs/PayPal.log',
			'log.LogLevel' => 'DEBUG',
			//'log.AdapterFactory' => 'MonologLogFactory', // Factory class implementing \PayPal\Log\PayPalLogFactory
			'cache.enabled' => true,
			'cache.FileName' => '../cache/PayPal.cache', // for determining paypal cache directory
			//'http.CURLOPT_CONNECTTIMEOUT' => 60,
			//'http.headers.PayPal-Partner-Attribution-Id' => '123123123',
		]
	]
];