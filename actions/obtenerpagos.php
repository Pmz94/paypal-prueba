<?php

require_once '../Funciones.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
	try {
		$servicios = include_once '../config/servicios.php';

		if(!$servicios->db || $servicios->db == false) throw new Exception();

		$query = "
            SELECT
                t.id,
                t.id_transaccion,
                t.id_venta,
                t.fechahora,
                p.nombre producto,
                COALESCE((t.pago_total / t.cantidad), 0) AS precio,
                COALESCE(t.cantidad, 0) AS cantidad,
                t.pago_total,
                CONCAT(c.nombre, ' ', c.apellido) comprador,
                c.correo,
                e.estado,
                t.devuelto
            FROM transacciones t
            LEFT JOIN compradores c
                ON t.clave_comprador = c.clave
            LEFT JOIN productos p
                ON t.id_producto = p.id
            LEFT JOIN estadosdepago e
                ON t.id_estado = e.id
			ORDER BY t.fechahora DESC
		";

		$querycount = "
			SELECT COUNT(*) count
		    FROM ({$query}) t
		";

		$count = 0;
		$querycount = $servicios->db->prepare($querycount);
		$querycount->execute();
		foreach($querycount->fetchAll(PDO::FETCH_ASSOC) as $i => $row) {
			$count = intval($row['count']);
		}

		if($count == false || $count <= 0) throw new Exception('No hay datos', 404);

		$data = [];
		$query = $servicios->db->prepare($query);
		$query->execute();
		$result = $query->fetchAll(PDO::FETCH_ASSOC);

		if(!$result || $result == null || $result == false) throw new Exception('No hay transacciones', 404);

		foreach($result as $i => $row) {
			$data[] = $row;
		}

		$func = new Funciones();
		$data = $func->convertir_de_latin1_a_utf8_recursivo($data);

		$output = [
			'status' => true,
			'code' => 200,
			'count' => $count,
			'data' => $data
		];
		header('HTTP/1.1 200 OK; Content-Type: application/json; charset=UTF-8;');
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