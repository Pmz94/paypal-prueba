<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<link rel="shortcut icon" type="image/x-icon" href="asset/img/favicon.ico">
	<title>Pagos</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/css/dataTables.bootstrap4.css">
	<link rel="stylesheet" href="asset/css/style.css">
</head>

<body>
	<div class="container text-center">
		<div class="UIpagos">
			<div class="box-pagos">
				<h1><strong>Pagos realizados</strong></h1>
				<hr>
				<div class="table-responsive">
					<table id="tablaPagos" class="table table-striped table-bordered table-sm table-hover">
						<thead>
							<tr>
								<th>Fecha</th>
								<th>Hora</th>
								<th>Comprador</th>
								<th>Total</th>
								<th>Venta</th>
								<th>Estado</th>
								<th>Ver</th>
								<th>Devolucion</th>
							</tr>
						</thead>
					</table>
				</div>
				<hr>
				<a href="estadisticas.php" class="btn btn-paypal-2">Estadisticas</a>
				<a href="index.html" class="btn btn-paypal-2">Regresar al inicio</a>
				<br>
			</div>
			<br>
		</div>

		<!--Modal de detalles de pago-->
		<div class="modal fade" id="pagosModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Detalles de pago</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<table class="table table-bordered table-striped table-sm">
							<thead>
								<tr>
									<th colspan="2">Recibo</th>
								</tr>
							</thead>
							<tbody>
								<tr id="idTransaccion"></tr>
								<tr id="idCarrito"></tr>
								<tr id="correo"></tr>
								<tr id="idVenta"></tr>
								<tr id="producto"></tr>
								<tr id="precio"></tr>
								<tr id="cantidad"></tr>
								<tr id="total"></tr>
								<tr id="fecha"></tr>
								<tr id="hora"></tr>
								<tr id="estado"></tr>
								<tr id="fechaDev"></tr>
								<tr id="horaDev"></tr>
							</tbody>
						</table>
						<hr>
						<pre id="payment" class="pre-scrollable text-left"></pre>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-paypal-2" data-dismiss="modal">Cerrar</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Modal para confirmar devolucion -->
		<!--<div class="modal fade" id="devolucionModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Confirmar devolucion</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						Seguro que quieres reembolsar este cargo?
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-paypal-2" id="modal-btn-si">Devolver</button>
						<button type="button" class="btn btn-paypal-2" data-dismiss="modal" id="modal-btn-no">Cancelar</button>
					</div>
				</div>
			</div>
		</div>-->
	</div>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/dataTables.bootstrap4.js"></script>
	<script src="asset/js/script.js"></script>
	<script type="text/javascript">
		$(function() {
			var dataTable = $('#tablaPagos').DataTable({
				autoWidth: true,
				searching: false,
				ordering: false,
				processing: true,
				pagingType: 'full',
				lengthMenu: [[10, 15, 20], [10, 15, 20]],
				serverSide: true,
				ajax: {
					url: 'obtenerpagos.php',
					type: 'POST'
				},
				columnDefs: [
					{
						targets: [1, 3, 5, 6],
						orderable: false

					}
				],
				language: {
					info: 'Pag. _PAGE_ de _PAGES_',
					infoEmpty: 'No hay datos',
					infoFiltered: '(de los _MAX_ renglones)',
					lengthMenu: 'Mostrar _MENU_ renglones por pagina',
					loadingRecords: 'Cargando datos...',
					processing: 'Procesando...',
					search: 'Buscar:',
					zeroRecords: 'No se encontro nada',
					paginate: {
						first: '&#171;',
						last: '&#187;',
						next: '&#8250;',
						previous: '&#8249;'
					}
				}
			});

			/*var tablaStats = $('#tablaStats').dataTable({
				ajax: {
					url: 'estadisticas.php',
					type: 'POST'
				},
				columns: [
					{ data: 'mejorComprador' },
					{ data: 'fechaUltimoPago' },
					{ data: 'completos' },
					{ data: 'pendientes' },
					{ data: 'devueltos' },
					{ data: 'totalPagos' }
				]
			});*/

			$(document).on('click', '.view', function() {
				var idTransaccion = $(this).attr('id');
				$.ajax({
					url: 'vercadapago.php',
					method: 'POST',
					data: {idTransaccion: idTransaccion},
					dataType: 'json',
					success: function(data) {
						$('#pagosModal').modal('show');

						$('#idTransaccion').html('<th>ID de Transaccion:</th><td>' + idTransaccion + '</td>');
						$('#idCarrito').html('<th>ID de Carrito:</th><td>' + data.idCarrito + '</td>');
						$('#correo').html('<th>Comprador:</th><td>' + data.correo + '</td>');
						$('#idVenta').html('<th>ID de Venta:</th><td>' + data.idVenta + '</td>');
						$('#producto').html('<th>Producto:</th><td>' + data.producto + '</td>');
						$('#precio').html('<th>Precio/Unidad:</th><td>$' + data.precio + '</td>');
						$('#cantidad').html('<th>Cantidad:</th><td>' + data.cantidad + '</td>');
						$('#total').html('<th>Total:</th><td>$' + data.total + '</td>');
						$('#fecha').html('<th>Fecha:</th><td>' + data.fecha + '</td>');
						$('#hora').html('<th>Hora:</th><td>' + data.hora + '</td>');

						if(data.estado === 'refunded') {
							$('#estado').html('<th style="background-color:red;color:white;">Estado:</th><td style="background-color:red;color:white;">' + data.estado + '</td>');
							$('#fechaDev').html('<th style="background-color:red;color:white;">Fecha:</th><td style="background-color:red;color:white;">' + data.fechaDev + '</td>');
							$('#horaDev').html('<th style="background-color:red;color:white;">Hora:</th><td style="background-color:red;color:white;">' + data.horaDev + '</td>');
						} else {
							$('#estado').html('<th>Estado:</th><td>' + data.estado + '</td>');
							$('#fechaDev').html('');
							$('#horaDev').html('');
						}

						$('#payment').text(data.data);
					}
				})
			});

			/*
			$(document).on('click', '.refund', function() {
				$('#devolucionModal').modal('show');
				var idVenta = $(this).attr('id');
				if($('#modal-btn-si').data('clicked')) {
					$.ajax({
						url: 'reembolsarpago.php',
						method: 'POST',
						data: { idVenta: idVenta },
						success: function(data) {
							alert(data);
							$('#devolucionModal').modal('hide');
							dataTable.ajax.reload();
						}
					});
				} else {
					return false;
				}
			});
			*/

			$(document).on('click', '.refund', function() {
				var idVenta = $(this).attr('id');
				if(confirm('Seguro que quieres reembolsar este cargo?')) {
					$.ajax({
						url: 'reembolsarpago.php',
						method: 'POST',
						data: {idVenta: idVenta},
						success: function(data) {
							alert(data);
							dataTable.ajax.reload();
							//tablaStats.ajax.reload();
						}
					});
				} else {
					return false;
				}
			});

			/*$('#stats').on('click', function() {
				$.ajax({
					type: 'GET',
					url: 'estadisticas.php',
					success: function(data) {
						$('#div1').html(data);
					}
				});
			});*/
		});
	</script>
</body>

</html>