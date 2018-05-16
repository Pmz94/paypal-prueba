<!DOCTYPE html>
<html lang = "es">

<head>
	<meta charset = "UTF-8">
	<meta name = "viewport" content = "width=device-width, initial-scale=1.0">
	<meta http-equiv = "X-UA-Compatible" content = "ie=edge">
	<link rel = "shortcut icon" type = "image/x-icon" href = "asset/img/favicon.ico">
	<title>Exito</title>
	<link rel = "stylesheet" href = "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.0/css/bootstrap.css">
	<link rel = "stylesheet" href = "asset/css/style.css">
</head>

<div class = "container">
	<?php require 'cargarpago.php' ?>
</div>

<body>
	<div class = "container text-center">
		<h1><strong>Pago realizado</strong></h1>
		<table class = "table table-striped table-bordered table-sm">
			<thead>
				<tr>
					<th colspan = "2">Recibo</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>ID de transaccion</td>
					<td><?php echo $paymentId; ?></td>
				</tr>
				<tr>
					<td>Comprador</td>
					<td><?php echo $payment->payer->payer_info->email ?></td>
				</tr>
				<tr>
					<td>ID de venta</td>
					<td><?php echo $payment->transactions[0]->related_resources[0]->sale->id ?></td>
				</tr>
				<tr>
					<td>Producto</td>
					<td><?php echo $payment->transactions[0]->item_list->items[0]->name ?></td>
				</tr>
				<tr>
					<td>Precio/Unidad</td>
					<td><?php echo '$' . $payment->transactions[0]->item_list->items[0]->price ?></td>
				</tr>
				<tr>
					<td>Cantidad</td>
					<td><?php echo $payment->transactions[0]->item_list->items[0]->quantity ?></td>
				</tr>
				<tr>
					<td>Total</td>
					<td><?php echo '$' . $payment->transactions[0]->amount->total ?></td>
				</tr>
				<tr>
					<td>Fecha:</td>
					<td><?php echo $fecha; ?></td>
				</tr>
				<tr>
					<td>Hora:</td>
					<td><?php echo $hora; ?></td>
				</tr>
			</tbody>
		</table>
		<hr>
		<h3>Archivo JSON =</h3>
		<pre class = "pre-scrollable text-left"><?php echo $payment; ?></pre>
		<hr>
		<a href = "pagos.php" class = "btn btn-paypal-2">Ver otros movimientos</a>
		<a href = "index.html" class = "btn btn-paypal-2">Regresar al inicio</a>
		<br>
		<br>
	</div>
</body>

</html>