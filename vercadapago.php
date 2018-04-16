<?php

use PayPal\Api\Payment;

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

    $pago = $query->fetch(\PDO::FETCH_ASSOC);

    $fechahora = new DateTime($pago['fechahora']);
    $fecha = $fechahora->format('d/m/Y');
    $hora = $fechahora->format('h:i:sa');

    try {
        $payment = Payment::get($idTransaccion, $apiContext);
        //$params = array('count' => 200, 'start_index' => 5);
        //$payments = Payment::all($params, $apiContext);
    } catch(Exception $ex) {
        echo '<h1>Algo malio sal</h1><hr>';
        die($ex);
    }
    ?>

    <!--<pre class = "pre-scrollable text-left">
        <?php
    //foreach($payments->payments as $pagos) {
    //    echo $pagos->id . ',' . $pagos->create_time . '<br>';
    //}
    ?>
    </pre>-->

    <table class = "table table-bordered table-striped table-sm">
        <thead>
            <tr>
                <td colspan = "2">Recibo</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Transaccion</td>
                <td><?php echo $pago['idTransaccion'] ?></td>
            </tr>
            <tr>
                <td>Comprador</td>
                <td><?php echo $pago['correo'] ?></td>
            </tr>
            <tr>
                <td>Venta</td>
                <td><?php echo $payment->transactions[0]->related_resources[0]->sale->id ?></td>
            </tr>
            <tr>
                <td>Producto</td>
                <td><?php echo $payment->transactions[0]->item_list->items[0]->name ?></td>
            </tr>
            <tr>
                <td>Precio/Unidad</td>
                <td><?php echo '$' . $payment->transactions[0]->item_list->items[0]->price ?></td>
            </tr>
            <tr>
                <td>Cantidad</td>
                <td><?php echo $payment->transactions[0]->item_list->items[0]->quantity ?></td>
            </tr>
            <tr>
                <td>Subtotal</td>
                <td><?php echo '$' . $payment->transactions[0]->amount->details->subtotal ?></td>
            </tr>
            <tr>
                <td>Envio</td>
                <td><?php echo '$' . $payment->transactions[0]->amount->details->shipping ?></td>
            </tr>
            <tr>
                <td>Total</td>
                <td><?php echo '$' . $payment->transactions[0]->amount->total ?></td>
            </tr>
            <tr>
                <td>Fecha</td>
                <td><?php echo $fecha ?></td>
            </tr>
            <tr>
                <td>Hora</td>
                <td><?php echo $hora ?></td>
            </tr>
            <tr>
                <td>Estado</td>
                <td><?php echo $payment->transactions[0]->related_resources[0]->sale->state ?></td>
            </tr>
        </tbody>
    </table>
    <pre class = "pre-scrollable text-left">
        <?php echo $payment ?>
    </pre>

    <?php
    /*
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
    }*/
}