<?php

use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

session_start();

require 'app/credentials.php';

if(!isset($_GET['success'], $_GET['paymentId'], $_GET['token'], $_GET['PayerID'])) {
    die();
}

if((bool)$_GET['success'] == false) {
    echo 'El pago no se hizo o ya estaba hecho <br>';
    var_dump($payment);
    die();
}

$paymentId = $_GET['paymentId'];
$token = $_GET['token'];
$payerID = $_GET['PayerID'];
$invoiceNumber = $_SESSION['invoiceNumber'];
date_default_timezone_set('America/Hermosillo');
$fecha = date('d/m/Y');
$hora = date('h:i:sa');

$payment = Payment::get($paymentId, $paypal);

$execute = new PaymentExecution();
$execute->setPayerId($payerID);

try {
    $result = $payment->execute($execute, $paypal);

    $db = new PDO('mysql:host=localhost;dbname=paypalprueba', 'root', '');

    $query = $db->prepare('
        INSERT INTO transacciones (idTransaccion, idCarrito, idComprador, invoiceNumber, fechahora, data)
        VALUES (:idTransaccion, :idCarrito, :idComprador, :invoiceNumber, :fechahora, :data)
    ');

    $query->execute([
        'idTransaccion' => $paymentId,
        'idCarrito' => $token,
        'idComprador' => $payerID,
        'invoiceNumber' => $invoiceNumber,
        'fechahora' => date('Y-m-d H:i:s'),
        'data' => $payment
    ]);
    //var_dump($payment);
} catch(Exception $ex) {
    echo '<br>';
    $data = json_decode($ex->getData());
    var_dump($data);
    echo $data->message;
    echo '<br><a href = ' . APP_PATH . ' class = "btn btn-paypal-2">Regresar al inicio</a>';
    echo ' ';
    echo '<a href = ' . APP_PATH . "/pagos.php" . ' class = "btn btn-paypal-2">Ver movimientos</a>';
    die();
}