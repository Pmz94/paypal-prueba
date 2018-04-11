<!DOCTYPE html>
<html lang = "es">

<head>
    <meta charset = "UTF-8">
    <meta name = "viewport" content = "width=device-width, initial-scale=1.0">
    <meta http-equiv = "X-UA-Compatible" content = "ie=edge">
    <link rel = "shortcut icon" type = "image/x-icon" href = "asset/img/favicon.ico">
    <title>Pagos</title>
    <link rel = "stylesheet" href = "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0/css/bootstrap.css">
    <link rel = "stylesheet" href = "asset/css/style.css">
</head>

<div class = "container">
    <?php require 'obtenerpagos.php'; ?>
</div>

<body>
    <div class = "container text-center">
        <h1><strong>Pagos realizados</strong></h1>
        <table class = "table table-striped table-bordered table-sm">
            <thead>
                <tr>
                    <td>Transaccion</td>
                    <td>Carrito</td>
                    <td>Comprador</td>
                    <td>Fecha</td>
                    <td colspan = "2">Opciones</td>
                </tr>
            </thead>
            <tbody>
                <?php foreach($transacciones as $row) { ?>
                    <tr>
                        <td><?php echo $row['idTransaccion']; ?></td>
                        <td><?php echo $row['idCarrito']; ?></td>
                        <td><?php echo $row['correo']; ?></td>
                        <td><?php echo $row['fechahora']; ?></td>
                        <td>
                            <a href = "pagos.php?idTransaccion=<?php echo $row['idTransaccion'] ?>&idCarrito=<?php echo $row['idCarrito'] ?>&idComprador=<?php echo $row['idComprador'] ?>" class = "btn btn-paypal-2">Ver</a>
                        </td>
                        <td><a href = "#" class = "btn btn-paypal-2">Cancelar</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <hr>
        <?php include 'vercadapago.php'; ?>
        <hr>
        <a href = "<?php echo APP_PATH; ?>" class = "btn btn-paypal-2">Regresar al inicio</a>
        <br>
        <br>
    </div>
</body>

</html>
