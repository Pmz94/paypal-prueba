<?php

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

require 'vendor/autoload.php';

define('APP_PATH', 'http://localhost/Proyectos/paypal');

$paypal = new ApiContext(new OAuthTokenCredential(
    'ARVvSQ00ePRN-qu2llfeACneCX9-01Obls_25Iy6bCzZlhBXvGOM1g7K8HZRJqmqMsKI6shdiqZI3AoT',
    'EPxihHAv9gr6BU2Ipa6xpkHCtP1xnP3K5SM2i1-iAZk4cM6v9ZclVVeLYTzbpldhBr76ixjT04uFyC_G'
));

$paypal->setConfig([
    'mode' => 'sandbox',
    'log.LogEnabled' => true,
    'log.FileName' => 'PayPal.log',
    'log.LogLevel' => 'DEBUG',
    //'cache.enabled' => true,
    //'cache.FileName' => '/PaypalCache' // for determining paypal cache directory
    //'http.CURLOPT_CONNECTTIMEOUT' => 30
    //'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
    //'log.AdapterFactory' => '\PayPal\Log\DefaultLogFactory' // Factory class implementing \PayPal\Log\PayPalLogFactory
]);