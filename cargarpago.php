<?php

use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

session_start();

require 'app/credentials.php';

if(!isset($_GET['success'], $_GET['paymentId'], $_GET['token'], $_GET['PayerID'])) {
	die();
}

if((bool)$_GET['success'] == false) {
	echo 'El pago no se hizo o ya estaba hecho <br>';
	var_dump($payment);
	die();
}

$paymentId = $_GET['paymentId'];
$token = $_GET['token'];
$payerID = $_GET['PayerID'];

$idCarrito = str_replace('EC-', '', $token);
date_default_timezone_set('America/Hermosillo');
$fecha = date('d/m/Y');
$hora = date('h:i:sa');

$payment = Payment::get($paymentId, $apiContext);

$execute = new PaymentExecution();
$execute->setPayerId($payerID);

try {
	$result = $payment->execute($execute, $apiContext);
	$servicio = 0;

	switch($payment->transactions[0]->item_list->items[0]->name) {
		case 'Inscripcion':
			$servicio = 1;
			break;
		case 'Materia':
			$servicio = 2;
			break;
		case 'Colegiatura':
			$servicio = 3;
			break;
		case 'Idioma':
			$servicio = 4;
			break;
		case 'Credencial':
			$servicio = 5;
			break;
		case 'Examen de Regularizacion':
			$servicio = 6;
			break;
		case 'Materias Sueltas':
			$servicio = 7;
			break;
		case 'Certificado Parcial':
			$servicio = 8;
			break;
		case 'Examen de Pasantía':
			$servicio = 9;
			break;
		case 'Duplicado de Certificado':
			$servicio = 10;
			break;
		case 'Cursos de Verano':
			$servicio = 11;
			break;
		case 'Constancias':
			$servicio = 12;
			break;
		case 'Kárdex':
			$servicio = 13;
			break;
		case 'Folletos Sueltos':
			$servicio = 14;
			break;
		case 'Resello de Credencial':
			$servicio = 15;
			break;
	}

	switch($payment->transactions[0]->related_resources[0]->sale->state) {
		case 'completed':
			$estado = 1;
			break;
		case 'pending':
			$estado = 3;
			break;
	}

	include 'app/conexion.php';

	$query2 = $db->prepare('
		INSERT INTO compradores (idComprador, correo, nombre, apellido, telefono)
		SELECT * FROM (SELECT :idComprador, :correo, :nombre, :apellido, :telefono) tmp
		WHERE NOT EXISTS (
			SELECT idComprador FROM compradores WHERE idComprador = :idComprador
		)
	');
	$query2->execute([
		'idComprador' => $payerID,
		'correo' => $payment->payer->payer_info->email,
		'nombre' => $payment->payer->payer_info->first_name,
		'apellido' => $payment->payer->payer_info->last_name,
		'telefono' => strval($payment->payer->payer_info->phone)
	]);

	$query = $db->prepare('
        INSERT INTO transacciones (sistema_pago, idTransaccion, idComprador, idVenta, servicio, pagoTotal, fechahora, estado, data)
        VALUES (:sistema_pago, :idTransaccion, :idComprador, :idVenta, :servicio, :pagoTotal, :fechahora, :estado, :data)
    ');
	$query->execute([
		'sistema_pago' => 'paypal',
		'idTransaccion' => $paymentId,
		'idComprador' => $payerID,
		'idVenta' => $payment->transactions[0]->related_resources[0]->sale->id,
		'servicio' => $servicio,
		'pagoTotal' => $payment->transactions[0]->amount->total,
		'fechahora' => date('Y-m-d H:i:s'),
		'estado' => $estado,
		'data' => $payment
	]);
} catch(Exception $ex) {
	echo '<br>';
	$data = json_decode($ex->getData());
	var_dump($data);
	echo $data->message;
	echo '<br><a href = "index.html" class = "btn btn-paypal-2">Regresar al inicio</a>';
	echo ' ';
	echo '<a href = "pagos.php" class = "btn btn-paypal-2">Ver movimientos</a>';
	die();
}