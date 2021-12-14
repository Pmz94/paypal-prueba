<?php

return new Phalcon\Config(array(
	'database' => array(
		'adapter' => 'Mysql',
		'host' => 'localhost',
		'port' => 3306,
		'username' => 'root',
		'password' => '',
		'dbname' => 'paypalprueba',
		'schema' => 'public',
	),
	'application' => array(
		'baseUri' => 'http://localhost/paypal-prueba',
		'funcionesDir' => APP_PATH . '/funciones/',
		'pluginsDir' => APP_PATH . '/plugins/',

		'controllersDir' => APP_PATH . '/controllers/',
		'modelsDir' => APP_PATH . '/models/',
		'viewsDir' => APP_PATH . '/views/',

		'defaultLanguage' => 'es',
	),
	'paypal_credentials' => array(
		'client_id' => '',
		'secret' => '',
		'settings' => array(
			'mode' => 'sandbox',
			'http.ConnectionTimeOut' => 60,
			'log.LogEnabled' => false,
			'log.FileName' => APP_PATH . '/logs/paypal.log',
			'log.LogLevel' => 'FINE'
		),
		'webhook_id' => ''
	),
));