<?php

include 'app/conexion.php';

$query = $db->prepare('
	SELECT
		c.correo mejorComprador,
		MAX(t.fechahora) ultimoPago,
		IFNULL(comp.completos, 0) completos,
		IFNULL(pend.pendientes, 0) pendientes,
		IFNULL(dev.devoluciones, 0) devueltos,
		COUNT(t.pagoTotal) totalPagos,
		MIN(t.pagoTotal) pagoMin,
		MAX(t.pagoTotal) pagoMax,
		ROUND(AVG(t.pagoTotal), 2) gastosProm,
		ROUND(STD(t.pagoTotal), 2) desvEstGastos,
		COALESCE((SUM(t.pagoTotal) - totalDevuelto), SUM(t.pagoTotal)) totalGastado,
		IFNULL(dev.totalDevuelto, 0) totalDevuelto
	FROM transacciones t
		LEFT JOIN compradores c
		USING (idComprador)
		LEFT JOIN (
			SELECT idComprador, COUNT(*) completos
			FROM transacciones
			WHERE estado = 1 AND devuelto = 0
			GROUP BY idComprador
		) comp
		USING (idComprador)
		LEFT JOIN (
			SELECT idComprador, COUNT(*) pendientes
			FROM transacciones
			WHERE estado = 3 AND devuelto = 0
			GROUP BY idComprador
		) pend
		USING (idComprador)
		LEFT JOIN (
			SELECT idComprador, SUM(devuelto) devoluciones, SUM(pagoTotal) totalDevuelto
			FROM transacciones
			WHERE devuelto = 1
			GROUP BY idComprador
		) dev
		USING (idComprador)
	GROUP BY mejorComprador
	HAVING totalPagos >= 1
	ORDER BY totalPagos DESC, totalGastado DESC 
');

$query->execute();

$estadisticas = $query->fetchAll(\PDO::FETCH_ASSOC);
$data = [];

foreach($estadisticas as $row) {
	$sub_array = [];
	$sub_array['mejorComprador'] = $row['mejorComprador'];
	$sub_array['fechaUltimoPago'] = date_format(date_create($row['ultimoPago']), 'd/m/Y h:ia');
	$sub_array['completos'] = $row['completos'];
	$sub_array['pendientes'] = $row['pendientes'];
	$sub_array['devueltos'] = $row['devueltos'];
	$sub_array['totalPagos'] = $row['totalPagos'];
	$sub_array['pagoMin'] = $row['pagoMin'];
	$sub_array['pagoMax'] = $row['pagoMax'];
	$sub_array['promGastos'] = $row['gastosProm'];
	$sub_array['desvEstGastos'] = $row['desvEstGastos'];
	$sub_array['totalGastado'] = $row['totalGastado'];
	$sub_array['totalDevuelto'] = $row['totalDevuelto'];
	$data[] = $sub_array;
}

//echo json_encode($data, \JSON_PRETTY_PRINT);