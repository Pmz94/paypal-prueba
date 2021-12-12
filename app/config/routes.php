<?php

$router = new Phalcon\Mvc\Router(false);

$router->removeExtraSlashes(true);

$router->setDefaults(array(
	'controller' => 'Index',
	'action' => 'route404'
));

$router->add('/', array(
	'controller' => 'Index',
	'action' => 'index'
));

$router->add('/:controller', array(
	'controller' => 1,
	'action' => 'index'
));

$router->add('/:controller/:action', array(
	'controller' => 1,
	'action' => 2
));

$router->add("/:controller/:action/:params", array(
	"controller" => 1,
	"action" => 2,
	"params" => 3
));

$router->notFound(array(
	'controller' => 'Index',
	'action' => 'route404'
));

return $router;