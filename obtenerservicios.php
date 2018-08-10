<?php

$db = include_once 'app/conexion.php';
$query = $db->prepare('
	SELECT *
	FROM servicios
');

$query->execute();

$result = $query->fetchAll(\PDO::FETCH_ASSOC);
$count = $query->rowCount();

$output = [
	'recordsTotal' => $count,
	'recordsFiltered' => $count,
	'data' => $result
];

echo json_encode($output, JSON_PRETTY_PRINT);