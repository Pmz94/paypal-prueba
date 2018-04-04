<!DOCTYPE html>
<html lang = "es">

<head>
    <meta charset = "UTF-8">
    <meta name = "viewport" content = "width=device-width, initial-scale=1.0">
    <meta http-equiv = "X-UA-Compatible" content = "ie=edge">
    <link rel = "shortcut icon" type = "image/x-icon" href = "asset/img/favicon.ico">
    <title>Exito</title>
    <link rel = "stylesheet" href = "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0/css/bootstrap.css">
    <link rel = "stylesheet" href = "asset/css/style.css">
</head>

<div class = "container">
    <?php require 'payexec.php'; ?>
</div>

<body>
    <div class = "container text-center">
        <h1><strong>Pago realizado</strong></h1>
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
                    <td>ID de comprador:</td>
                    <td><?php echo $payerID ?></td>
                </tr>
                <tr>
                    <td>Invoice Number:</td>
                    <td><?php echo $invoiceNumber ?></td>
                </tr>
                <tr>
                    <td>Fecha:</td>
                    <td><?php echo $fecha ?></td>
                </tr>
                <tr>
                    <td>Hora:</td>
                    <td><?php echo $hora ?></td>
                </tr>
            </tbody>
        </table>
        <hr>
        <h3>$payment =</h3>
        <pre class = "pre-scrollable text-left"><?php echo $payment ?></pre>
        <hr>
        <a href = "<?php echo APP_PATH ?>" class = "btn btn-paypal-2">Regresar al inicio</a>
        <br>
        <br>
    </div>
</body>

</html>