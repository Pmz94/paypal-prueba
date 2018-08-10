<?php
/**
 * Created by PhpStorm.
 * User: CSWebPmz
 * Date: 04/07/2018
 * Time: 03:05 PM
 */

//header('Content-Type: json');
//$test = utf8_encode($_POST['payment']); // Don't forget the encoding
//$payment = json_decode($test);

$id = $_POST['id'];
$event = $_POST['event'];
$status = $_POST['status'];
$auth_code = $_POST['auth_code'];
$reference = $_POST['reference'];
$total = $_POST['total'];
$hash = $_POST['hash'];
$plan[] = $_POST['plan'];

$arreglo = [];
$arreglo['id'] = $id;
$arreglo['event'] = $event;
$arreglo['status'] = $status;
$arreglo['auth_code'] = $auth_code;
$arreglo['reference'] = $reference;
$arreglo['total'] = $total;
$arreglo['hash'] = $hash;
//$arreglo['hashed'] = hash_hmac('sha256', '14', $hash);
$arreglo['plan'] = $plan;

$data[] = $arreglo;

$json = json_encode($data, JSON_PRETTY_PRINT);

file_put_contents('C:\Users\CSWebPmz\Desktop\noti.json', $json);

$db = include_once 'app/conexion.php';

$query = $db->prepare('
	INSERT INTO transacciones (sistema_pago, idTransaccion, idVenta, pagoTotal, fechahora, estado, fechahoraAct, devuelto, fechahoraDev, data)
		VALUES
	(:sistema_pago, :idTransaccion, :idVenta, :pagoTotal, :fechahora, :estado, :fechahoraAct, :devuelto, :fechahoraDev, :data)
');

$query->execute([
	'sistema_pago' => 'banwire',
	'idTransaccion' => $id,
	'pagoTotal' => $total,
	'fechahora' => date('Y-m-d H:i:s'),
	'data' => $json
]);