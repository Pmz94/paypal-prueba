<!DOCTYPE html>
<html lang = "es">

<head>
	<meta charset = "UTF-8">
	<meta name = "viewport" content = "width=device-width, initial-scale=1.0">
	<meta http-equiv = "X-UA-Compatible" content = "ie=edge">
	<link rel = "shortcut icon" type = "image/x-icon" href = "asset/img/favicon.ico">
	<title>Estadisticas de pagos</title>
	<link rel = "stylesheet" href = "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.0/css/bootstrap.css">
	<link rel = "stylesheet" href = "https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.16/css/dataTables.bootstrap4.css">
	<link rel = "stylesheet" href = "asset/css/style.css">
</head>

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

/*$data = [];
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
}*/
//echo json_encode($data, \JSON_PRETTY_PRINT);
?>

<body>
	<div class = "container text-center">
		<div class = "UIpagos">
			<div class = "box-pagos">
				<h2><strong>Mejores Compradores</strong></h2>
				<hr>
				<table id = "tablaStats" class = "table table-striped table-bordered table-sm table-hover">
					<thead>
						<tr>
							<th>Comprador</th>
							<th>Ultimo pago</th>
							<th>Completos</th>
							<th>Pendientes</th>
							<th>Devueltos</th>
							<th>Total Pagos</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($estadisticas as $row) { ?>
							<tr>
								<td><?php echo $row['mejorComprador'] ?></td>
								<td><?php echo date_format(date_create($row['ultimoPago']), 'd/m/Y h:ia') ?></td>
								<td><?php echo $row['completos'] ?></td>
								<td><?php echo $row['pendientes'] ?></td>
								<td><?php echo $row['devueltos'] ?></td>
								<td><?php echo $row['totalPagos'] ?></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
				<hr>
				<table class = "table table-striped table-bordered table-sm table-hover">
					<thead>
						<tr>
							<th>Comprador</th>
							<th>Pago min</th>
							<th>Pago max</th>
							<th>x&#x0304;</th>
							<th>&sigma;</th>
							<th>Total Gastado</th>
							<th>Total Devuelto</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($estadisticas as $row) { ?>
							<tr>
								<td><?php echo $row['mejorComprador'] ?></td>
								<td><?php echo '$' . $row['pagoMin'] ?></td>
								<td><?php echo '$' . $row['pagoMax'] ?></td>
								<td><?php echo '$' . $row['gastosProm'] ?></td>
								<td><?php echo '$' . $row['desvEstGastos'] ?></td>
								<td><?php echo '$' . $row['totalGastado'] ?></td>
								<td><?php echo '$' . $row['totalDevuelto'] ?></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
				<hr>
				<a href = "pagos.php" class = "btn btn-paypal-2">Ver Pagos</a>
				<a href = "index.html" class = "btn btn-paypal-2">Regresar al inicio</a>
				<br>
			</div>
		</div>
	</div>
	<br>
	<script src = "https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js"></script>
	<script src = "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.0/js/bootstrap.js"></script>
	<script src = "https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.16/js/jquery.dataTables.js"></script>
	<script src = "https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.16/js/dataTables.bootstrap4.js"></script>
	<script src = "asset/js/script.js"></script>
</body>

</html>