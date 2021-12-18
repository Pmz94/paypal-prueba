<?php

$loader = new Phalcon\Loader();

$loader->registerNamespaces([
	'Funciones' => $config->application->funcionesDir,
	'Plugins' => $config->application->pluginsDir,
	'Models' => $config->application->modelsDir,
	'Controllers' => $config->application->controllersDir,
	'Views' => $config->application->viewsDir,
])->register();