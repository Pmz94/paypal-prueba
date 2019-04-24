<?php

use PayPal\Api\Payer;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Details;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
	try {
		$servicios = require_once  '../config/servicios.php';

		$id_producto = ($_POST['id_producto'] > 0) ? $_POST['id_producto'] : null;
		$producto = (trim($_POST['producto']) != '') ? trim($_POST['producto']) : null;
		$precio = ($_POST['precio'] >= 0) ? $_POST['precio'] : null;
		$cantidad = ($_POST['cantidad'] > 0) ? $_POST['cantidad'] : null;

		if(!$id_producto && !$producto && !$precio && !$cantidad) {
			$output = [
				'status' => false,
				'code' => 422,
				'message' => 'Llene todos los campos'
			];
			header('HTTP/1.1 422 Missing parameters; Content-Type: application/json; charset=UTF-8');
			echo json_encode($output, JSON_PRETTY_PRINT);
		}

		$total = $precio * $cantidad;

		$payer = new Payer();
		$payer->setPaymentMethod('paypal');

		$item = new Item();
		$item->setSku($id_producto)->setName($producto)->setQuantity($cantidad)->setPrice($precio)->setCurrency('MXN');

		$itemlist = new ItemList();
		$itemlist->setItems([$item]);

		$amount = new Amount();
		$amount->setTotal($total)->setCurrency('MXN');

		$transaction = new Transaction();
		$transaction->setItemList($itemlist)->setAmount($amount)->setDescription('Pagando un producto');

		$redirectUrls = new RedirectUrls();
		$redirectUrls->setReturnUrl(BASE_URI . '/actions/cargarpago.php?status=true')->setCancelUrl(BASE_URI);

		$payment = new Payment();
		$payment->setIntent('sale')->setPayer($payer)->setTransactions([$transaction])->setRedirectUrls($redirectUrls);

		$payment->create($servicios->paypal);

		$output = [
			'status' => true,
			'code' => 200,
			'url' => $payment->getApprovalLink(),
			'payment' => $payment->toArray()
		];
		header('HTTP/1.1 200 OK; Content-type: application/json; charset=UTF-8');
		echo json_encode($output, JSON_PRETTY_PRINT);
//		header('Location: ' . $payment->getApprovalLink());
	} catch(Exception $e) {
		$output = [
			'status' => false,
			'code' => $e->getCode(),
			'message' => $e->getMessage()
		];
		header('HTTP/1.1 400 Exception');
		//		echo json_encode($output, JSON_PRETTY_PRINT);
		die($e);
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