<?php

$db = require_once 'app/conexion.php';
$query = $db->prepare('
	SELECT *
	FROM servicios
');

$query->execute();


$result = $query->fetchAll(\PDO::FETCH_ASSOC);
$data = [];
$filtered_rows = $query->rowCount();

foreach($result as $row) {
	$sub_array = [];
	$sub_array[] = date_format(date_create($row['fechahora']), 'd/m/Y');
	$sub_array[] = date_format(date_create($row['fechahora']), 'h:ia');
	$sub_array[] = $row['correo'];
	$sub_array[] = '$' . $row['pagoTotal'];
	$sub_array[] = $row['idVenta'];
	$sub_array[] = $row['estado'];
	$sub_array[] = '<button name = "view" id = "' . $row['idTransaccion'] . '" class = "btn btn-paypal-2 btn-sm view">Ver</button>';

	if($row['devuelto'] == 1) {
		$sub_array[] = '<button class = "btn btn-paypal-2 btn-sm" disabled>Devuelto</button>';
	} else {
		$sub_array[] = '<button name = "refund" id = "' . $row['idVenta'] . '" class = "btn btn-paypal-2 btn-sm refund">Devolucion</button>';
	}

	$data[] = $sub_array;
}

$output = [
	'draw' => intval($_POST['draw']),
	'recordsTotal' => $filtered_rows,
	'recordsFiltered' => $todastransacciones,
	'data' => $data,
];

echo json_encode($output);