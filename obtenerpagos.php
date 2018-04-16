<?php

use PayPal\Api\Payment;

include 'app/credentials.php';

try {
    include 'app/conexion.php';

    $payments = $db->prepare('
        SELECT t.idTransaccion, idComprador, idVenta, c.correo, t.fechahora
            FROM transacciones t
            JOIN compradores c USING (idComprador)
        ORDER BY t.fechahora DESC
    ');
    $payments->execute();
    $transacciones = $payments->fetchAll(\PDO::FETCH_ASSOC);

} catch(Exception $ex) {
    echo '<h1>Algo malio sal</h1><hr>';
    die($ex);
}