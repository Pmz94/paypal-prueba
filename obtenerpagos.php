<?php

use PayPal\Api\Payment;

require 'app/credentials.php';
//require 'crearpago.php';

try {
    //$params = array('count' => 10, 'start_index' => 5);
    //$payments = Payment::all($params, $paypal);

    $db = new PDO('mysql:host=localhost;dbname=paypalprueba', 'root', '');

    $payments = $db->prepare('
        SELECT t.idTransaccion, t.idCarrito, idComprador, c.correo, t.invoiceNumber, t.fechahora
            FROM transacciones t
            JOIN compradores c USING (idComprador)
        ORDER BY t.fechahora DESC
    ');
    $payments->execute();
    $transacciones = $payments->fetchAll(\PDO::FETCH_ASSOC);

    //print_r($transacciones['1']);
    //var_dump($transacciones);
    //echo json_encode($transacciones);

} catch(Exception $ex) {
    echo '<h1>Algo malio sal</h1><hr>';
    die($ex);
}