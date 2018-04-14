<?php

include 'app/credentials.php';

try {
    include 'app/conexion.php';

    $payments = $db->prepare('
        SELECT t.idTransaccion, t.idCarrito, idComprador, c.correo, t.fechahora
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