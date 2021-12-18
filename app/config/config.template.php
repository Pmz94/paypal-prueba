<?php

return new Phalcon\Config([
	'database' => [
		'adapter' => 'Mysql',
		'host' => 'localhost',
		'port' => 3306,
		'username' => 'root',
		'password' => '',
		'dbname' => 'paypalprueba',
		'schema' => 'public',
	],
	'application' => [
		'baseUri' => 'http://localhost/paypal-prueba',
		'funcionesDir' => APP_PATH . '/funciones/',
		'pluginsDir' => APP_PATH . '/plugins/',

		'controllersDir' => APP_PATH . '/controllers/',
		'modelsDir' => APP_PATH . '/models/',
		'viewsDir' => APP_PATH . '/views/',

		'defaultLanguage' => 'es',
	],
	'paypal_credentials' => [
		'client_id' => '',
		'secret' => '',
		'webhook_id' => '',
		'settings' => [
			'mode' => 'sandbox',
			'http.ConnectionTimeOut' => 60,
			'log.LogEnabled' => false,
			'log.FileName' => APP_PATH . '/logs/paypal.log',
			'log.LogLevel' => 'FINE'
		]
	]
]);