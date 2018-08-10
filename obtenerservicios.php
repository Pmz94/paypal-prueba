<?php

$db = include_once 'app/conexion.php';
$query = $db->prepare('
	SELECT *
	FROM servicios
');

$query->execute();

$result = $query->fetchAll(\PDO::FETCH_ASSOC);
$data = [];
$count = $query->rowCount();

foreach($result as $row) {
	$sub_array = [];
	$sub_array[] = $row['idServicio'];
	$sub_array[] = $row['nomServicio'];
	$sub_array[] = $row['importe'];

	$data[] = $sub_array;
}

$output = [
	'recordsTotal' => $count,
	'recordsFiltered' => $count,
	'data' => $data,
];

echo json_encode($output, JSON_PRETTY_PRINT);