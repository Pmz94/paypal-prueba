<?php

use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

if($_SERVER['REQUEST_METHOD'] == 'GET') {
	try {
		$servicios = include_once '../config/servicios.php';

		if(!isset($_GET['status'], $_GET['paymentId'], $_GET['token'], $_GET['PayerID'])) {
			throw new Exception('Parametros incorrectos');
		}

		if((bool)$_GET['status'] == false) {
			throw new Exception('El pago no se hizo o ya estaba hecho');
		}

		$paymentId = ($_GET['paymentId'] != '') ? $_GET['paymentId'] : null;
		$payerID = ($_GET['PayerID'] != '') ? $_GET['PayerID'] : null;
		$token = ($_GET['token'] != '') ? $_GET['token'] : null;

		$payment = Payment::get($paymentId, $servicios->paypal);

		$execute = new PaymentExecution();
		$execute->setPayerId($payerID);

		$payment = $payment->execute($execute, $servicios->paypal);

		switch(strtolower($payment->transactions[0]->related_resources[0]->sale->state)) {
			case 'completed': $id_estado = 1; break;
			case 'pending': $id_estado = 3; break;
		}

		$new_buyer = "
			INSERT INTO compradores(clave, correo, nombre, apellido, telefono)
			SELECT *
			FROM (
			    SELECT
			        :clave AS clave,
			        :correo AS correo,
			        :nombre AS nombre,
			        :apellido AS apellido,
			        :telefono AS telefono
			) tmp
			WHERE NOT EXISTS (
				SELECT c.clave
				FROM compradores c
				WHERE c.clave = tmp.clave
			);
		";
		$values = [
			'clave' => $payerID,
			'correo' => $payment->payer->payer_info->email,
			'nombre' => $payment->payer->payer_info->first_name,
			'apellido' => $payment->payer->payer_info->last_name,
			'telefono' => strval($payment->payer->payer_info->phone)
		];
		$new_buyer = $servicios->db->prepare($new_buyer);
		$new_buyer->execute($values);

		$query = "
            INSERT INTO transacciones(id_transaccion, id_venta, id_comprador, clave_comprador, id_producto, cantidad, subtotal, envio, fechahora, id_estado)
            VALUES
                (:id_transaccion, :id_venta, (SELECT id FROM compradores WHERE UPPER(clave) = :clave_comprador), :clave_comprador, :id_producto, :cantidad, :subtotal, :envio, NOW(), (SELECT id FROM estadosdepago WHERE LOWER(estado) = :estado));
        ";
		$values2 = [
			'id_transaccion' => $paymentId,
			'clave_comprador' => strtoupper($payerID),
			'id_venta' => $payment->transactions[0]->related_resources[0]->sale->id,
			'id_producto' => $payment->transactions[0]->item_list->items[0]->sku,
			'cantidad' => $payment->transactions[0]->item_list->items[0]->quantity,
			'subtotal' => $payment->transactions[0]->amount->details->subtotal ?? $payment->transactions[0]->amount->total,
			'envio' => $payment->transactions[0]->amount->details->shipping ?? 0,
			'estado' => strtolower($payment->transactions[0]->related_resources[0]->sale->state)
		];
		$query = $servicios->db->prepare($query);
		$query->execute($values2);

		$first = (trim($payment->payer->payer_info->first_name) != '') ? $payment->payer->payer_info->first_name . ' ' : '';
		$middle = (trim($payment->payer->payer_info->middle_name) != '') ? $payment->payer->payer_info->middle_name . ' ' : '';
		$last = (trim($payment->payer->payer_info->last_name) != '') ? $payment->payer->payer_info->last_name : '';

		$nombre_comprador = $first . $middle . $last;

		$pago = json_encode([
			'id_transaccion' => ['name' => 'ID de transaccion', 'value' => $payment->id],
			'id_venta' => ['name' => 'ID de venta', 'value' => $payment->transactions[0]->related_resources[0]->sale->id],
			'id_comprador' => ['name' => 'ID de comprador', 'value' => $payment->payer->payer_info->payer_id],
			'nombre_comprador' => ['name' => 'Comprador', 'value' => $nombre_comprador],
			'email_comprador' => ['name' => 'Correo', 'value' => $payment->payer->payer_info->email],
			'producto' => ['name' => 'Producto', 'value' => $payment->transactions[0]->item_list->items[0]->name],
			'precio_unidad' => ['name' => 'Precio/Unidad', 'value' => '$'.$payment->transactions[0]->item_list->items[0]->price],
			'cantidad' => ['name' => 'Cantidad', 'value' => $payment->transactions[0]->item_list->items[0]->quantity],
			'total' => ['name' => 'Total', 'value' => '$'.$payment->transactions[0]->amount->total],
			'fecha' => ['name' => 'Fecha', 'value' => date('d/m/Y')],
			'hora' => ['name' => 'Hora', 'value' => date('H:i:sa')]
		]);

		$redirectView = BASE_URI . '/views/pagorealizado.html?recibo=' . $pago . '&token=' . $token;

		header('Location: ' . $redirectView);

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