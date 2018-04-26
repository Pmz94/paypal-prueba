<!DOCTYPE html>
<html lang = "es">

<head>
    <meta charset = "UTF-8">
    <meta name = "viewport" content = "width=device-width, initial-scale=1.0">
    <meta http-equiv = "X-UA-Compatible" content = "ie=edge">
    <link rel = "shortcut icon" type = "image/x-icon" href = "asset/img/favicon.ico">
    <title>Pagos</title>
    <link rel = "stylesheet" href = "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.0/css/bootstrap.css">
    <link rel = "stylesheet" href = "https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.16/css/dataTables.bootstrap4.css">
    <link rel = "stylesheet" href = "asset/css/style.css">
</head>

<div class = "container">
    <?php include 'app/credentials.php'; ?>
</div>

<body>
    <div class = "container text-center">
        <div class = "UIpagos">
            <div class = "box-pagos">
                <h1><strong>Pagos realizados</strong></h1>
                <hr>
                <div class = "table-responsive">
                    <table id = "tablaPagos" class = "table table-striped table-bordered table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Total</th>
                                <th>Comprador</th>
                                <th>Venta</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Devuelto</th>
                                <th>Ver</th>
                                <th>Devolucion</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <hr>
                <a href = "<?php echo APP_PATH ?>" class = "btn btn-paypal-2">Regresar al inicio</a>
                <br>
            </div>
        </div>

        <!--Modal de detalles de pago-->
        <div class = "modal fade" id = "pagosModal" tabindex = "-1" role = "dialog" aria-labelledby = "exampleModalLabel" aria-hidden = "true">
            <div class = "modal-dialog" role = "document">
                <div class = "modal-content">
                    <div class = "modal-header">
                        <h5 class = "modal-title">Detalles de pago</h5>
                        <button type = "button" class = "close" data-dismiss = "modal" aria-label = "Close">
                            <span aria-hidden = "true">&times;</span>
                        </button>
                    </div>
                    <div class = "modal-body">
                        <table class = "table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th colspan = "2">Recibo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id = "idTransaccion"></tr>
                                <tr id = "idCarrito"></tr>
                                <tr id = "correo"></tr>
                                <tr id = "idVenta"></tr>
                                <tr id = "producto"></tr>
                                <tr id = "precio"></tr>
                                <tr id = "cantidad"></tr>
                                <tr id = "total"></tr>
                                <tr id = "fecha"></tr>
                                <tr id = "hora"></tr>
                                <tr id = "estado"></tr>
                                <tr id = "fechaDev"></tr>
                                <tr id = "horaDev"></tr>
                            </tbody>
                        </table>
                        <hr>
                        <pre id = "payment" class = "pre-scrollable text-left"></pre>
                    </div>
                    <div class = "modal-footer">
                        <button type = "button" class = "btn btn-paypal-2" data-dismiss = "modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src = "https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js"></script>
    <script src = "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.0/js/bootstrap.js"></script>
    <script src = "https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.16/js/jquery.dataTables.js"></script>
    <script src = "https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.16/js/dataTables.bootstrap4.min.js"></script>
    <script src = "asset/js/script.js"></script>
</body>

</html>
