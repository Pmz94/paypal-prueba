<?php

use PayPal\Api\Payment;

/*
if(!isset($_GET['idTransaccion'], $_GET['idComprador'])) {
    echo '<h5>Selecciona un pago para verlo</h5>';
} else {
    $idTransaccion = $_GET['idTransaccion'];
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

    $pago = $query->fetchAll(\PDO::FETCH_ASSOC);

    $fechahora = new DateTime($pago['fechahora']);
    $fecha = $fechahora->format('d/m/Y');
    $hora = $fechahora->format('h:i:sa');

    try {
        $payment = Payment::get($idTransaccion, $apiContext);
    } catch(Exception $ex) {
        echo '<h1>Algo malio sal</h1><hr>';
        die($ex);
    }
    ?>

    <table id = "tablarecibo" class = "table table-bordered table-striped table-sm" style = "width: auto;">
        <thead>
            <tr>
                <th colspan = "2">Recibo</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>Transaccion</th>
                <td><?php echo $pago['idTransaccion'] ?></td>
            </tr>
            <tr>
                <th>Carrito</th>
                <td><?php echo $pago['idCarrito'] ?></td>
            </tr>
            <tr>
                <th>Comprador</th>
                <td><?php echo $pago['correo'] ?></td>
            </tr>
            <tr>
                <th>Venta</th>
                <td><?php echo $payment->transactions[0]->related_resources[0]->sale->id ?></td>
            </tr>
            <tr>
                <th>Producto</th>
                <td><?php echo $payment->transactions[0]->item_list->items[0]->name ?></td>
            </tr>
            <tr>
                <th>Precio/Unitario</th>
                <td><?php echo '$' . $payment->transactions[0]->item_list->items[0]->price ?></td>
            </tr>
            <tr>
                <th>Cantidad</th>
                <td><?php echo $payment->transactions[0]->item_list->items[0]->quantity ?></td>
            </tr>
            <tr>
                <th>Total</th>
                <td><?php echo '$' . $payment->transactions[0]->amount->total ?></td>
            </tr>
            <tr>
                <th>Fecha</th>
                <td><?php echo $fecha ?></td>
            </tr>
            <tr>
                <th>Hora</th>
                <td><?php echo $hora ?></td>
            </tr>
            <tr>
                <th>Estado</th>
                <td><?php echo $payment->transactions[0]->related_resources[0]->sale->state ?></td>
            </tr>
        </tbody>
    </table>
    <pre class = "pre-scrollable text-left">
        <?php echo $payment ?>
    </pre>

    <?php
}
*/

include 'app/conexion.php';
include 'app/credentials.php';

if(isset($_POST['idTransaccion'])) {

    $idTransaccion = $_POST['idTransaccion'];

    $output = [];

    $query = $db->prepare('
        SELECT *
        FROM transacciones t
        JOIN compradores c USING (idComprador)
        WHERE t.idTransaccion = :idTransaccion
        LIMIT 1
    ');

    $query->execute([
        'idTransaccion' => $idTransaccion
    ]);
    $pago = $query->fetchAll(\PDO::FETCH_ASSOC);

    try {
        $payment = Payment::get($idTransaccion, $apiContext);
    } catch(Exception $ex) {
        echo '<h1>Algo malio sal</h1><hr>';
        die($ex);
    }

    /*$data = $payment;

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
    }*/

    foreach($pago as $row) {
        $output['idTransaccion'] = $row['idTransaccion'];
        $output['idCarrito'] = $row['idCarrito'];
        $output['correo'] = $row['correo'];
        $output['idVenta'] = $row['idVenta'];
        //$output['idVenta'] = $payment->transactions[0]->related_resources[0]->sale->id;
        $output['producto'] = $payment->transactions[0]->item_list->items[0]->name;
        $output['precio'] = $payment->transactions[0]->item_list->items[0]->price;
        $output['cantidad'] = $payment->transactions[0]->item_list->items[0]->quantity;
        $output['total'] = $payment->transactions[0]->amount->total;
        $output['fecha'] = date_format(date_create($row['fechahora']), 'd/m/Y');
        $output['hora'] = date_format(date_create($row['fechahora']), 'h:i:sa');
        $output['estado'] = $payment->transactions[0]->related_resources[0]->sale->state;
        $output['data'] = '' . $payment;
    }
    echo json_encode($output);
}