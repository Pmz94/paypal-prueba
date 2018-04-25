<?php

include 'app/credentials.php';
/*
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

//echo json_encode($transacciones);
*/

include 'app/conexion.php';

$allpayments = $db->prepare('
    SELECT * FROM transacciones
');
$allpayments->execute();
$todastransacciones = $allpayments->rowCount();

$query = '';
$output = [];
$query = '
    SELECT *
    FROM transacciones t
    JOIN compradores c USING (idComprador) 
';

if(isset($_POST['order'])) {
    $query .= '
        ORDER BY ' . $_POST['order']['0']['column'] . ' ' . $_POST['order']['0']['dir'] . ' 
    ';
} else {
    $query .= '
        ORDER BY t.fechahora DESC 
    ';
}

if($_POST['length'] != -1) {
    $query .= 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
}

$payments = $db->prepare($query);
$payments->execute();
$transacciones = $payments->fetchAll(\PDO::FETCH_ASSOC);
$data = [];
$filtered_rows = $payments->rowCount();

foreach($transacciones as $row) {
    $sub_array = [];
    $sub_array[] = '$' . $row['pagoTotal'];
    $sub_array[] = $row['correo'];
    $sub_array[] = $row['idVenta'];
    $sub_array[] = date_format(date_create($row['fechahora']), 'd/m/Y');
    $sub_array[] = date_format(date_create($row['fechahora']), 'h:i:sa');
    //$sub_array[] = $row['fechahora'];
    $sub_array[] = $row['devuelto'];
    $sub_array[] = '<button name = "view" id = "' . $row['idTransaccion'] . '" class = "btn btn-paypal-2 btn-sm view">Ver</button>';
    if($row['devuelto'] == 1) {
        $sub_array[] = '<button name = "refund" id = "' . $row['idVenta'] . '" class = "btn btn-paypal-2 btn-sm refund" disabled>Devuelto</button>';
    } else {
        $sub_array[] = '<button name = "refund" id = "' . $row['idVenta'] . '" class = "btn btn-paypal-2 btn-sm refund">Devolucion</button>';
    }
    $data[] = $sub_array;
}

$output = array(
    'draw' => intval($_POST['draw']),
    'recordsTotal' => $filtered_rows,
    'recordsFiltered' => $todastransacciones,
    'data' => $data,
);

echo json_encode($output);