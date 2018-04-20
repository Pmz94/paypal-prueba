<!DOCTYPE html>
<html lang = "es">

<head>
    <meta charset = "UTF-8">
    <meta name = "viewport" content = "width=device-width, initial-scale=1.0">
    <meta http-equiv = "X-UA-Compatible" content = "ie=edge">
    <link rel = "shortcut icon" type = "image/x-icon" href = "asset/img/favicon.ico">
    <title>Pagos</title>
    <link rel = "stylesheet" href = "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.0/css/bootstrap.css">
    <link rel = "stylesheet" href = "https://cdn.datatables.net/1.10.16/css/jquery.dataTables.css">
    <link rel = "stylesheet" href = "asset/css/style.css">
</head>

<div class = "container">
    <?php require 'obtenerpagos.php'; ?>
</div>

<body>
    <div class = "container text-center">
        <h1><strong>Pagos realizados</strong></h1>
        <!--<div style = "height:547px; overflow:auto;">-->
            <table id = "tablatransacc" class = "table table-striped table-bordered table-sm table-hover display">
                <thead>
                    <tr>
                        <th>Transaccion</th>
                        <th>Comprador</th>
                        <th>Venta</th>
                        <th>Fecha</th>
                        <th colspan = "2">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($transacciones as $row) { ?>
                        <tr>
                            <td><?php echo $row['idTransaccion'] ?></td>
                            <td><?php echo $row['correo'] ?></td>
                            <td><?php echo $row['idVenta'] ?></td>
                            <td><?php echo $row['fechahora'] ?></td>
                            <td>
                                <a href = "pagos.php?&idTransaccion=<?php echo $row['idTransaccion'] ?>&idComprador=<?php echo $row['idComprador'] ?>" class = "btn btn-paypal-2">Ver</a>
                            </td>
                            <td>
                                <a href = "reembolsarpago.php?idVenta=<?php echo $row['idVenta'] ?>" class = "btn btn-paypal-2">Devolucion</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <!--</div>-->
        <hr>
        <a href = "<?php echo APP_PATH ?>" class = "btn btn-paypal-2">Regresar al inicio</a>
        <br>
        <!--Modal de detalles de pago-->
        <?php include 'vercadapago.php' ?>
        <br>
    </div>
    <script src = "https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js"></script>
    <script src = "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.0/js/bootstrap.js"></script>
    <script src = "https://cdn.datatables.net/1.10.16/js/jquery.dataTables.js"></script>
    <script src = "asset/js/script.js"></script>
</body>

</html>
