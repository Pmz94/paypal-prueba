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

$payment = Payment::get($paymentId, $paypal);

$execute = new PaymentExecution();
$execute->setPayerId($payerID);

try {
    $result = $payment->execute($execute, $paypal);
} catch(Exception $e) {
    $data = json_decode($e->getData());
    var_dump($data);
    echo $data->message;
    die();
}
?>

<!DOCTYPE html>
<html lang = "es">

<head>
    <meta charset = "UTF-8">
    <meta name = "viewport" content = "width=device-width, initial-scale=1.0">
    <meta http-equiv = "X-UA-Compatible" content = "ie=edge">
    <title>Exito</title>
    <link rel = "stylesheet" href = "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0/css/bootstrap.css">
    <link rel = "stylesheet" href = "css/style.css">
</head>

<body>
    <div class = "container text-center">
        <h1>Pago realizado</h1>
        <table class = "table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Variable</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>ID de pago:</td>
                    <td><?php echo $paymentId ?></td>
                </tr>
                <tr>
                    <td>ID de payer</td>
                    <td><?php echo $payerID ?></td>
                </tr>
            </tbody>
        </table>
        <hr>
        <h3>$payment =</h3>
        <pre class = "pre-scrollable text-left"><?php echo $payment ?></pre>
        <hr>
        <a href = "<?php echo APP_PATH ?>" class = "btn btn-success">Regresar al inicio</a>
        <br>
        <br>
    </div>
</body>

</html>