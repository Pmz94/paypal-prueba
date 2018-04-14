<?php

if(!isset($_GET['idTransaccion'], $_GET['idCarrito'], $_GET['idComprador'])) {
    echo '<h5>Selecciona un pago para verlo</h5>';
} else {
    $idTransaccion = $_GET['idTransaccion'];
    $idCarrito = $_GET['idCarrito'];
    $idComprador = $_GET['idComprador'];

    include 'app/conexion.php';

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

    $fechahora = new DateTime($pago['fechahora']);
    $fecha = $fechahora->format('d/m/Y');
    $hora = $fechahora->format('h:i:sa');

    //var_dump($pago);
    //print_r($pago[0]['idTransaccion']);

    //$payment = Payment::get($idTransaccion, $apiContext);
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
                <td>Fecha</td>
                <td><?php echo $fecha ?></td>
            </tr>
            <tr>
                <td>Hora</td>
                <td><?php echo $hora ?></td>
            </tr>
        </tbody>
    </table>
    <pre class = "pre-scrollable text-left">
        <?php echo $pago['data']; ?>
    </pre>

    <?php
    $data = $pago['data'];

    //no asociativo
    $manage = json_decode($data);
    echo $manage->transactions[0]->related_resources[0]->sale->id . '<br>';

    //asociativo
    $manage = json_decode($data, true);
    echo $manage['transactions'][0]['related_resources'][0]['sale']['id'] . '<br>';

    //para recorrer los datos de un json
    foreach($manage as $idx => $obj) {
        if($idx == 'transactions') {
            foreach($obj as $idx2 => $obj2) {
                foreach($obj2 as $idx3 => $obj3) {
                    if($idx3 == 'related_resources') {
                        foreach($obj3 as $idx4 => $obj4) {
                            foreach($obj4 as $idx5 => $obj5) {
                                if($idx5 == 'sale') {
                                    foreach($obj5 as $idx6 => $obj6) {
                                        if($idx6 == 'id') {
                                            echo $obj6;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

}