<?php

if($_SERVER['REQUEST_METHOD'] == 'POST') {
	try {
		$servicios = include_once '../config/servicios.php';

		$query = "
			SELECT
				c.correo mejor_comprador,
				MAX(t.fechahora) ultimo_pago,
				COALESCE(comp.completos, 0) completos,
				COALESCE(pend.pendientes, 0) pendientes,
				COALESCE(dev.devoluciones, 0) devueltos,
				COUNT(t.subtotal) total_pagos,
				MIN(t.subtotal) pago_min,
				MAX(t.subtotal) pago_max,
				ROUND(AVG(t.subtotal), 2) gastos_prom,
				ROUND(STD(t.subtotal), 2) desvest_gastos,
				COALESCE((SUM(t.subtotal) - total_devuelto), SUM(t.subtotal)) total_gastado,
				COALESCE(dev.total_devuelto, 0) total_devuelto
			FROM transacciones t
			LEFT JOIN compradores c
				ON t.clave_comprador = c.clave
			LEFT JOIN (
				SELECT
					clave_comprador,
					COUNT(*) completos
				FROM transacciones
				WHERE id_estado = 1
					AND devuelto = 0
				GROUP BY clave_comprador
			) comp
				ON t.clave_comprador = comp.clave_comprador
			LEFT JOIN (
				SELECT
					clave_comprador,
					COUNT(*) pendientes
				FROM transacciones
				WHERE id_estado = 3
					AND devuelto = 0
				GROUP BY clave_comprador
			) pend
				ON t.clave_comprador = pend.clave_comprador
			LEFT JOIN (
				SELECT
					clave_comprador,
					SUM(devuelto) devoluciones,
					SUM(subtotal) total_devuelto
				FROM transacciones
				WHERE devuelto = 1
				GROUP BY clave_comprador
			) dev
				ON t.clave_comprador = dev.clave_comprador
			GROUP BY mejor_comprador
			HAVING total_pagos >= 1
			ORDER BY total_pagos DESC, total_gastado DESC;
		";
		$query = $servicios->db->prepare($query);
		$query->execute();
		foreach($query->fetchAll(PDO::FETCH_ASSOC) as $i => $row) {
			$data[] = $row;
		}

		$output = [
			'status' => true,
			'code' => 200,
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