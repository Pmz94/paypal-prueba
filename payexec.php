<?php

use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

require 'app/credentials.php';

if(!isset($_GET['success'], $_GET['paymentId'], $_GET['PayerID'])) {
    die();
}

if((bool)$_GET['success'] == false) {
    die();
}

$paymentId = $_GET['paymentId'];
$payerID = $_GET['PayerID'];

date_default_timezone_set('America/Hermosillo');
$fecha = date('d/m/Y');
$hora = date('h:ia');

$payment = Payment::get($paymentId, $paypal);

$execute = new PaymentExecution();
$execute->setPayerId($payerID);

try {
    $result = $payment->execute($execute, $paypal);
} catch(Exception $e) {
    $data = json_decode($e->getData());
    var_dump($data);
    echo $data->message;
    echo '<br><a href = ' . APP_PATH . ' class = "btn btn-paypal-2">Regresar al inicio</a>';
    die();
}

// Create connection
$conexion = mysqli_connect('localhost', 'root', '', 'paypalprueba');

// Check connection
if(!$conexion) {
    die('Fallo la conexion: ' . mysqli_connect_error());
}

$query = "INSERT INTO transacciones (paymentId, payerID, fecha, hora, data) VALUES ('$paymentId', '$payerID', '$fecha', '$hora','$payment')";

if(mysqli_query($conexion, $query)) {
    //echo '<script type="text/javascript">alert("Se agrego la transferencia a la BD");</script>';
} else {
    die('Error: ' . $query . '<br>' . mysqli_error($conexion));
}
mysqli_close($conexion);