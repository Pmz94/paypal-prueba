<?php

require_once '../Funciones.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
	try {
		$servicios = include_once '../config/servicios.php';
		$conn = $servicios->db;

		if(!$conn || $conn == false) throw new Exception();

		$query = "
			SELECT *
			FROM productos
		";

		$query = $conn->prepare($query);
		$query->execute();

		//while($row = $query->fetch(PDO::FETCH_ASSOC)) {
		//	$data[] = $row;
		//}
		foreach($query->fetchAll(PDO::FETCH_ASSOC) as $i => $row) {
			$data[] = $row;
		}

		$func = new Funciones();
		$data = $func->convertir_de_latin1_a_utf8_recursivo($data);

		$output = [
			'status' => true,
			'code' => 200,
			'count' => count($data),
			'data' => $data
		];
		header('HTTP/1.1 200 OK; Content-type: application/json; charset=UTF-8');
		echo json_encode($output, JSON_PRETTY_PRINT);

	} catch(Exception $e) {
		$output = [
			'status' => false,
			'code' => $e->getCode(),
			'message' => $e->getMessage()
		];
		header('HTTP/1.1 400 Exception');
		echo json_encode($output, JSON_PRETTY_PRINT);
	}
} else {
	$output = [
		'status' => false,
		'code' => 405,
		'message' => 'Nel'
	];
	header('HTTP/1.1 405 Method not allowed');
	echo json_encode($output, JSON_PRETTY_PRINT);
}