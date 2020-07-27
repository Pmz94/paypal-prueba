<?php

$json = file_get_contents('php://input');
$webhook = json_decode($json);

date_default_timezone_set('America/Hermosillo');
$fechahora = date('Y-m-d H:i:s');
$evento = $webhook->event_type;
$idVenta = $webhook->resource->id;

//---Agregar webhook----------------------------------------------------------------------------------------------------------------------------------------------------
$db = new PDO('mysql:host=localhost;dbname=paypalprueba', 'root', '');

$query = $db->prepare('
	INSERT INTO webhooks (idWebhook, fechahora, tipoEvento, idTransaccion, data)
		VALUES
	(:idWebhook, :fechahora, :tipoEvento, :idTransaccion, :data)
');
$query->execute([
	'idWebhook' => $webhook->id,
	'fechahora' => $fechahora,
	'tipoEvento' => $evento,
	'idTransaccion' => $webhook->resource->parent_payment,
	'data' => json_encode($webhook, JSON_PRETTY_PRINT),
]);

switch($evento) {
	case 'PAYMENT.SALE.COMPLETED':
		try {
			$query = $db->prepare('
				UPDATE transacciones
				SET estado = 1, fechahoraAct = :fechahora
				WHERE idVenta = :idVenta
			');
			$query->execute([
				'fechahoraAct' => $fechahora,
				'idVenta' => $idVenta
			]);
			// file_put_contents('C:/xampp/htdocs/Proyectos/Webhooks/respuestas/preuba_COMPLETED_' . $query->rowCount() . '_columnas.txt', 'tipo: ' . $evento . '; id: ' . $idVenta . '; fecha: ' . $fechahora);
		} catch(Exception $e) {
			// file_put_contents('C:/xampp/htdocs/Proyectos/Webhooks/respuestas/preuba_COMPLETED_error.txt', 'Exception: ' . $e);
		}
		break;

	case 'PAYMENT.SALE.PENDING':
		try {
			$query = $db->prepare('
				UPDATE transacciones
				SET estado = 3, fechahoraAct = :fechahora
				WHERE idVenta = :idVenta
			');
			$query->execute([
				'fechahoraAct' => $fechahora,
				'idVenta' => $idVenta
			]);
			// file_put_contents('C:/xampp/htdocs/Proyectos/Webhooks/respuestas/preuba_PENDING_' . $query->rowCount() . '_columnas.txt', 'tipo: ' . $evento . '; id: ' . $idVenta . '; fecha: ' . $fechahora);
		} catch(Exception $e) {
			// file_put_contents('C:/xampp/htdocs/Proyectos/Webhooks/respuestas/preuba_PENDING_error.txt', 'Exception: ' . $e);
		}
		break;

	case 'PAYMENT.SALE.REFUNDED':
		try {
			$query = $db->prepare('
				UPDATE transacciones
				SET estado = 4, fechahoraAct = :fechahora, devuelto = 1, fechahoraDev = :fechahora
				WHERE idVenta = :idVenta
			');
			$query->execute([
				'fechahoraDev' => $fechahora,
				'idVenta' => $webhook->resource->sale_id
			]);
			// file_put_contents('C:/xampp/htdocs/Proyectos/Webhooks/respuestas/preuba_REFUNDED_' . $query->rowCount() . '_columnas.txt', 'tipo: ' . $evento . '; id: ' . $idVenta . '; fecha: ' . $fechahora);
		} catch(Exception $e) {
			// file_put_contents('C:/xampp/htdocs/Proyectos/Webhooks/respuestas/preuba_REFUNDED_error.txt', 'Exception: ' . $e);
		}
		break;
}

//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// file_put_contents('C:/xampp/htdocs/Proyectos/Webhooks/respuestas/notificacion.json', json_encode($webhook, JSON_PRETTY_PRINT));