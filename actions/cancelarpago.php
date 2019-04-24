<?php

use PayPal\Api\Amount;
use PayPal\Api\Refund;
use PayPal\Api\RefundRequest;
use PayPal\Api\Sale;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
	try {
		$servicios = include_once '../config/servicios.php';

		$saleId = ($_POST['id_venta'] != '') ? $_POST['id_venta'] : null;

		if($saleId == null) throw new Exception('No ID', 404);

		$sale = Sale::get($saleId, $servicios->paypal);

		$currency = $sale->amount->currency;
		$total = $sale->amount->total;

		$amount = new Amount();
		$amount->setCurrency($currency)->setTotal($total);

		$refundRequest = new RefundRequest();
		$refundRequest->setAmount($amount);

		$sale = new Sale();
		$sale->setId($saleId);

		$refundedSale = $sale->refundSale($refundRequest, $servicios->paypal);

		if($refundedSale) {

			$query = "
				UPDATE transacciones
                SET id_estado = 4,
                    fechahoraAct = :fechahoraDev,
                    devuelto = 1,
                    fechahora_cancelado = NOW()
                WHERE id_venta = :id_venta
			";

			$values = ['id_venta' => $saleId];

			$query = $servicios->db->prepare($query);
			$result = $query->execute($values);

			if($result) {
				$output = [
					'status' => true,
					'code' => 200,
					'message' => 'Transaccion cancelada'
				];
				header('HTTP/1.1 200 OK; Content-type: application/json; charset=UTF-8');
				echo json_encode($output, JSON_PRETTY_PRINT);
			} else throw new Exception();
		} else throw new Exception();

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