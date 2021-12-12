<?php

define('BASE_PATH', dirname(__DIR__));
const APP_PATH = BASE_PATH . '/app';

$config = include_once APP_PATH . '/config/config.php';
require_once APP_PATH . '/config/loader.php';
require_once APP_PATH . '/config/services.php';

// Use composer autoloader to load vendor classes
require_once __DIR__ . '/../vendor/autoload.php';

$app = new Phalcon\Mvc\Application();

$app->setDI($di);

try {

	$response = $app->handle();

	echo $response->getContent();

} catch(Exception $e) {
	echo 'Exception: ', $e->getMessage();
}