<?php

use PayPal\Api\Payment;

//include 'app/credentials.php';

if(!isset($_GET['idTransaccion'], $_GET['idCarrito'], $_GET['idComprador'])) {
    echo '<h5>Selecciona un pago para verlo</h5>';
} else {
    $idTransaccion = $_GET['idTransaccion'];
    $idCarrito = $_GET['idCarrito'];
    $idComprador = $_GET['idComprador'];

    $db = new PDO('mysql:host=localhost;dbname=paypalprueba', 'root', '');

    $query = $db->prepare('
        SELECT *
        FROM transacciones t
        JOIN compradores c USING (idComprador)
        WHERE t.idTransaccion = :idTransaccion
    ');

    $query->execute([
        'idTransaccion' => $idTransaccion
    ]);

    $pago = $query->fetch(\PDO::FETCH_ASSOC);

    //var_dump($pago);
    //print_r($pago[0]['idTransaccion']);

    //$payment = Payment::get($idTransaccion, $paypal);

    ?>
    <table class = "table table-bordered table-striped table-sm">
        <tbody>
            <tr>
                <td>Transaccion</td>
                <td><?php echo $pago['idTransaccion'] ?></td>
            </tr>
            <tr>
                <td>Carrito</td>
                <td><?php echo $pago['idCarrito'] ?></td>
            </tr>
            <tr>
                <td>Comprador</td>
                <td><?php echo $pago['correo'] ?></td>
            </tr>
            <tr>
                <td>Fecha y Hora</td>
                <td><?php echo $pago['fechahora'] ?></td>
            </tr>
        </tbody>
    </table>
    <pre class="pre-scrollable text-left">
        <?php echo $pago['data']; ?>
    </pre>
    <?php
}
