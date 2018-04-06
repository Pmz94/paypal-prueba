<?php

use PayPal\Api\Payment;

//require 'crearpago.php';

try {
    //$params = array('count' => 10, 'start_index' => 5);
    //$payments = Payment::all($params, $paypal);

    $db = new PDO('mysql:host=localhost;dbname=paypalprueba', 'root', '');

    $payments = $db->prepare('SELECT * FROM transacciones');
    $payments->execute();
    $transacciones = $payments->fetchAll();

    foreach($transacciones as $pago) {
        echo $pago;
    }

} catch(Exception $ex) {
    echo '<h1>Algo malio sal</h1><hr>';
    die($ex);
}

