<?php

include 'app/conexion.php';

$query = $db->prepare('
	SELECT
		c.correo mejorComprador,
		MAX(t.fechahora) ultimoPago,
		CASE WHEN comp.completos IS NULL THEN 0 ELSE comp.completos END completos,
		CASE WHEN pend.pendientes IS NULL THEN 0 ELSE pend.pendientes END pendientes,
		CASE WHEN dev.devoluciones IS NULL THEN 0 ELSE dev.devoluciones END devueltos,
		COUNT(t.pagoTotal) totalPagos,
		MIN(t.pagoTotal) pagoMin,
		MAX(t.pagoTotal) pagoMax,
		ROUND(AVG(t.pagoTotal), 2) gastosProm,
		ROUND(STD(t.pagoTotal), 4) devEstGastos,
		CASE WHEN (SUM(t.pagoTotal) - totalDevuelto) IS NULL THEN SUM(t.pagoTotal) ELSE (SUM(t.pagoTotal) - totalDevuelto) END totalGastado,
		CASE WHEN dev.totalDevuelto IS NULL THEN 0 ELSE dev.totalDevuelto END totalDevuelto
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
	ORDER BY totalGastado DESC, totalPagos DESC;
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
	$sub_array['devEstGastos'] = $row['devEstGastos'];
	$sub_array['totalGastado'] = $row['totalGastado'];
	$sub_array['totalDevuelto'] = $row['totalDevuelto'];
	$data[] = $sub_array;
}

//echo json_encode($data, \JSON_PRETTY_PRINT);