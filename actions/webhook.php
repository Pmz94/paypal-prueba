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

// file_put_contents('C:\Users\CSWebPmz\Desktop\noti.json', $json);

$servicios = include_once '../config/servicios.php';

$query = $servicios->db->prepare('
	INSERT INTO transacciones (id_transaccion, id_venta, subtotal, fechahora, id_estado, fechahoraAct, devuelto, fechahora_cancelado)
	VALUES
		(:id_transaccion, :id_venta, :subtotal, NOW(), (SELECT id FROM estadosdepago WHERE LOWER(estado) = :estado), :fechahoraAct, :devuelto, :fechahoraDev)
');

$query->execute([
	'id_transaccion' => $id,
	'subtotal' => $total
]);