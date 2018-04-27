<?php
/*
use PayPal\Api\Payment;

include 'app/credentials.php';

try {
    $params = ['count' => 20, 'page_size' => 30, 'sort_by' => 'create_time', 'start_id' => 'PAY-41P774494P8681235LK2EQ4A'];
    $payments = Payment::all($params, $apiContext);
} catch(Exception $ex) {
    echo '<h1>Algo malio sal</h1><hr>';
    die($ex);
}*/

$payments = file_get_contents('C:\Users\pmz94\Desktop\pastpayments.json');
$json = json_decode($payments, false); // decode the JSON into an associative array
//echo $json->payments[1]->id;
//var_dump($json);
$num = 1;
?>

    <table>
        <thead>
            <tr>
                <th>num</th>
                <th>idTransaccion</th>
                <th>idCarrito</th>
                <th>idComprador</th>
                <th>idVenta</th>
                <th>pagoTotal</th>
                <th>invoiceNumber</th>
                <th>fechahora</th>
                <th>devuelto</th>
                <th>fechahoraDev</th>
                <th>data</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($json->payments as $pago) { ?>
                <tr>
                    <td><?php echo $num ?></td>
                    <td><?php echo $pago->id ?></td>
                    <td><?php echo $pago->cart ?></td>
                    <td><?php echo $pago->payer->payer_info->payer_id ?></td>
                    <td><?php echo $pago->transactions[0]->related_resources[0]->sale->id ?></td>
                    <td><?php echo $pago->transactions[0]->amount->total ?></td>
                    <td><?php echo $pago->transactions[0]->invoice_number ?></td>
                    <td><?php echo date_format(date_create($pago->create_time), 'Y-m-d H:i:s'); ?></td>
                    <td><?php echo $pago->transactions[0]->related_resources[0]->sale->state ?></td>
                    <td><?php echo 'null' ?></td>
                    <td><?php echo 'json de API'; //json_encode($pago) ?></td>
                </tr>
                <?php $num++;
            } ?>
        </tbody>
    </table>
    <br>
<?php
$data = [];
$num = 0;
foreach($json->payments as $pago2) {
    $data[$num]['idTransaccion'] = $pago2->id;
    $data[$num]['idCarrito'] = $pago2->cart;
    $data[$num]['idComprador'] = $pago2->payer->payer_info->payer_id;
    $data[$num]['idVenta'] = $pago2->transactions[0]->related_resources[0]->sale->id;
    $data[$num]['pagoTotal'] = $pago2->transactions[0]->amount->total;
    $data[$num]['invoiceNumber'] = $pago2->transactions[0]->invoice_number;
    $data[$num]['fechahora'] = date_format(date_create($pago2->create_time), 'Y-m-d H:i:s');
    $data[$num]['devuelto'] = $pago2->transactions[0]->related_resources[0]->sale->state;
    $data[$num]['fechahoraDev'] = '';
    $data[$num]['data'] = json_encode($pago2, JSON_PRETTY_PRINT);
    $num++;
}

//var_dump($data);
//json_encode($data);

$sql = 'INSERT INTO transacciones (id, idTransaccion, idCarrito, idComprador, idVenta, pagoTotal, invoiceNumber, fechahora, devuelto, fechahoraDev, data) VALUES';

$num = 1;
$sqlv = '';
foreach($json->payments as $pago3) {
    $sqlv .= "(" . $num . ",'" . $pago3->id . "','" . $pago3->cart . "','" . $pago3->payer->payer_info->payer_id . "','" . $pago3->transactions[0]->related_resources[0]->sale->id . "'," . $pago3->transactions[0]->amount->total . ",'" . $pago3->transactions[0]->invoice_number . "','" . date_format(date_create($pago3->create_time), 'Y-m-d H:i:s') . "'," . 0 . ",'" . "','" . json_encode($pago3, JSON_PRETTY_PRINT) . "'),<br>";
    $num++;
}
echo $sql;
echo '<br>';
echo $sqlv;