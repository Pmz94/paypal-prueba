<?php

$di = new Phalcon\Di\FactoryDefault();

$di->set('config', $config);

$di->set('router', function() {
	$router = require_once 'routes.php';
	return $router;
});

$di->set('view', function() use ($config) {
	$view = new Phalcon\Mvc\View();
	$view->setViewsDir($config->application->viewsDir);
	return $view;
});

$di->set('dispatcher', function() use ($di) {
	$eventsManager = new Phalcon\Events\Manager();
	//Instanciar el plugin de seguridad
	//	$security = new \Plugins\AclPlugin($di);

	//Enviar todos los eventos producidos en el Dispatcher al plugin Security
	//	$eventsManager->attach('dispatch', $security);

	$dispatcher = new Phalcon\Mvc\Dispatcher();
	$dispatcher->setDefaultNamespace('Controllers');
	$dispatcher->setEventsManager($eventsManager);
	return $dispatcher;
});

$di->set('url', function() use ($config) {
	$url = new Phalcon\Mvc\Url();
	$url->setBaseUri($config->application->baseUri);
	return $url;
});

//$di->set('session', function() use ($config) {
//	$session = new \Phalcon\Session\Adapter\Files([
//		'uniqueId' => $config->session_id,
//	]);
//	$session->start();
//	return $session;
//});

$di->set('db', function() use ($config) {
	$motor = strtolower($config->database->adapter);
	$creds = array(
		'host' => $config->database->host,
		'port' => $config->database->port,
		'username' => $config->database->username,
		'password' => $config->database->password,
		'dbname' => $config->database->dbname,
		'schema' => $config->database->schema,
	);

	if($motor == 'mysql') $conexion = new Phalcon\Db\Adapter\Pdo\Mysql($creds);
	else if(in_array($motor, ['postgresql', 'postgres', 'pgsql', 'psql'])) $conexion = new Phalcon\Db\Adapter\Pdo\Postgresql($creds);
	else if($motor == 'sqlite') $conexion = new Phalcon\Db\Adapter\Pdo\Sqlite($creds);
	else throw new Exception('Motor de BD desconocido');

	return $conexion;
});

$di->set('application', $config->application);

/*$di->set('language', function() use ($di, $config) {
	$language = $di->getShared('session')->get('idioma');
	if(!$language || $language == '') {
		$language = $config->application->defaultLanguage;
	}
	return $language;
});*/

$di->set('security', function() {
	$security = new Phalcon\Security();
	$security->setDefaultHash(Phalcon\Security::CRYPT_BLOWFISH_A);
	// Set the password hashing factor to 12 rounds
	$security->setWorkFactor(12);
	return $security;
}, true);

$di->set('paypal', function() use ($config) {
	$clientId = $config->paypal_credentials->client_id;
	$secret = $config->paypal_credentials->secret;
	$settings[] = $config->paypal_credentials->settings;
	$webhookId  = $config->paypal_credentials->webhook_id;
	$apiContext = new PayPal\Rest\ApiContext(new PayPal\Auth\OAuthTokenCredential($clientId, $secret));
	$apiContext->setConfig($settings);
	return $apiContext;
});