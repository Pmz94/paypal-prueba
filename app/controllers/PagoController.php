<?php
/**
 * Created by PhpStorm.
 * User: Pedro Muñoz
 * Date: 18/09/2021
 * Time: 5:10 PM
 */

namespace Controllers;

use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\VerifyWebhookSignature;

class PagoController extends ControllerBase {

	public function indexAction() {
		$arrRecibido = $this->request->getQuery();

		$ok = false;
		$mensaje = '';
		$pago = [];

		try {
			// Estos son los parametros que paypal te debe de mandar
			if(!isset($arrRecibido['status'], $arrRecibido['paymentId'], $arrRecibido['token'], $arrRecibido['PayerID'])) throw new \Exception('Acceso incorrecto');

			$status = filter_var($arrRecibido['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
			$paymentId = $arrRecibido['paymentId'] ?: null;
			$token = $arrRecibido['token'] ?: null;

			if($status === false) throw new \Exception('El pago no se hizo o ya estaba hecho');

			if(!$paymentId) throw new \Exception('Pago no reconocido');

			// Ver si este pago existe
			$apiContext = $this->paypal;
			$payment = Payment::get($paymentId, $apiContext);

			$payerID = $payment->payer->payer_info->payer_id;
			$first = trim($payment->payer->payer_info->first_name) ?: '';
			$middle = trim($payment->payer->payer_info->middle_name) ?: '';
			$last = trim($payment->payer->payer_info->last_name) ?: '';

			if($middle) {
				$nombre_comprador = "${first} ${middle} ${last}";
			} else {
				$nombre_comprador = "${first} ${last}";
			}

			// Ver si aun no se ha ejecutado el pago
			if(!$payment->transactions[0]->related_resources) {
				$execution = new PaymentExecution();
				$execution->setPayerId($payerID);

				$result = $payment->execute($execution, $apiContext);

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
					'correo' => $result->payer->payer_info->email,
					'nombre' => $result->payer->payer_info->first_name,
					'apellido' => $result->payer->payer_info->last_name,
					'telefono' => strval($result->payer->payer_info->phone)
				];
				$this->db->execute($new_buyer, $values);

				$query = "
                    INSERT INTO transacciones(
                        id_transaccion,
                        id_venta,
                        id_comprador,
                        clave_comprador,
                        id_producto,
                        cantidad,
                        subtotal,
                        envio,
                        fechahora,
                        id_estado
                    )
                    VALUES (
                        :id_transaccion,
                        :id_venta,
                        (SELECT id FROM compradores WHERE UPPER(clave) = :clave_comprador),
                        :clave_comprador,
                        :id_producto,
                        :cantidad,
                        :subtotal,
                        :envio,
                        NOW(),
                        (SELECT id FROM estadosdepago WHERE LOWER(estado) = :estado)
                    );
                ";
				$values2 = [
					'id_transaccion' => $paymentId,
					'clave_comprador' => strtoupper($payerID),
					'id_venta' => $result->transactions[0]->related_resources[0]->sale->id,
					'id_producto' => $result->transactions[0]->item_list->items[0]->sku,
					'cantidad' => $result->transactions[0]->item_list->items[0]->quantity,
					'subtotal' => $result->transactions[0]->amount->details->subtotal ?: $result->transactions[0]->amount->total,
					'envio' => $result->transactions[0]->amount->details->shipping ?: 0,
					'estado' => strtolower($result->transactions[0]->related_resources[0]->sale->state)
				];
				$this->db->execute($query, $values2);

				// Obtener pago con propiedades actualizadas
				$payment = Payment::get($paymentId, $apiContext);
			}

			$estado = strtolower($payment->transactions[0]->related_resources[0]->sale->state);
			if($estado == 'completed') {
				$mensaje = 'Pago realizado con éxito';
			} else if($estado == 'pending') {
				$mensaje = 'El pago está en proceso';
			} else if($estado == 'denied') {
				$mensaje = 'El pago ha sido denegado';
			}
			// else { $mensaje = 'Se cancelo el pago'; }

			$pago = [
				'id_transaccion' => ['name' => 'ID de transaccion', 'value' => $payment->id],
				'id_venta' => ['name' => 'ID de venta', 'value' => $payment->transactions[0]->related_resources[0]->sale->id],
				'id_comprador' => ['name' => 'ID de comprador', 'value' => $payment->payer->payer_info->payer_id],
				'nombre_comprador' => ['name' => 'Comprador', 'value' => $nombre_comprador],
				'email_comprador' => ['name' => 'Correo', 'value' => $payment->payer->payer_info->email],
				'producto' => ['name' => 'Producto', 'value' => $payment->transactions[0]->item_list->items[0]->name],
				'precio_unidad' => ['name' => 'Precio/Unidad', 'value' => '$' . $payment->transactions[0]->item_list->items[0]->price],
				'cantidad' => ['name' => 'Cantidad', 'value' => $payment->transactions[0]->item_list->items[0]->quantity],
				'total' => ['name' => 'Total', 'value' => '$' . $payment->transactions[0]->amount->total],
				'fecha' => ['name' => 'Fecha', 'value' => date('d/m/Y')],
				'hora' => ['name' => 'Hora', 'value' => date('H:i:sa')]
			];

			$ok = true;
		} catch(\Exception $e) {
			$mensaje = $e->getMessage();
		}

		$this->view->setVars([
			'ok' => $ok,
			'mensaje' => $mensaje,
			'pago' => $pago
		]);
	}

	public function webhookpaypalAction() {
		$webhook = $cargoPagado = $result = [];
		try {
			$json = file_get_contents('php://input');

			$apiContext = $this->paypal;
			$webhookId  = $this->config->paypal_credentials->webhook_id;

			// Obtener parametros para verificar webhook
			$headers = getallheaders();
			$headers = array_change_key_case($headers, CASE_UPPER);
			$signatureVerification = new VerifyWebhookSignature();
			$signatureVerification->setAuthAlgo($headers['PAYPAL-AUTH-ALGO']);
			$signatureVerification->setTransmissionId($headers['PAYPAL-TRANSMISSION-ID']);
			$signatureVerification->setCertUrl($headers['PAYPAL-CERT-URL']);
			$signatureVerification->setWebhookId($webhookId);
			$signatureVerification->setTransmissionSig($headers['PAYPAL-TRANSMISSION-SIG']);
			$signatureVerification->setTransmissionTime($headers['PAYPAL-TRANSMISSION-TIME']);
			$signatureVerification->setRequestBody($json);
			//$request = clone $signatureVerification;

			// Verificar webhook
			$output = $signatureVerification->post($apiContext);

			//En caso de que no sea autentico
			if(strtoupper($output->getVerificationStatus()) == 'FAILURE') {
				return false;
			}

			$webhook = json_decode($json);
			date_default_timezone_set('America/Hermosillo');
			$evento = $webhook->event_type;
			$idTransaccion = $webhook->resource->parent_payment;

			// Obtener el json de la transaccion que se hizo
			$payment = Payment::get($idTransaccion, $apiContext);

			//Arreglo con parametros para BD
			$paymentArr = json_decode($payment, false);
			$cargoPagado = [];
			$cargoPagado['idTransaccion'] = $idTransaccion; //ID de transaccion
			$cargoPagado['idVenta'] = $webhook->resource->id; //ID de venta
			$cargoPagado['productos'] = $paymentArr->transactions[0]->item_list->items; //Arreglo de productos
			$cargoPagado['total'] = $webhook->resource->amount->total; //Total que se pago
			$cargoPagado['comision'] = $webhook->resource->transaction_fee->value; //Comision que cobra paypal
			$cargoPagado['estado'] = $webhook->resource->state; //Estado de pago
			$cargoPagado['fechahora'] = date('Y-m-d H:i:s'); //hora actual
			$cargoPagado['horaPaypal'] = $webhook->resource->update_time; //hora de paypal de la Venta
			$cargoPagado['evento'] = $evento;

			//Realizar accion dependiendo del evento que haya tenido el pago
			switch($evento) {
				case 'PAYMENT.SALE.COMPLETED':
					file_put_contents('C:/temp/'.$cargoPagado['estado'].'.json', json_encode($cargoPagado, JSON_PRETTY_PRINT));
					break;
				case 'PAYMENT.SALE.PENDING':
					file_put_contents('C:/temp/'.$cargoPagado['estado'].'.json', json_encode($cargoPagado, JSON_PRETTY_PRINT));
					break;
				case 'PAYMENT.SALE.REFUNDED' || 'PAYMENT.SALE.REVERSED':
					$cargoPagado['idDevolucion'] = $webhook->resource->id; //ID de devolucion
					$cargoPagado['idVenta'] = $webhook->resource->sale_id; //ID de venta
					file_put_contents('C:/temp/'.$cargoPagado['estado'].'.json', json_encode($cargoPagado, JSON_PRETTY_PRINT));
					break;
				case 'PAYMENT.SALE.DENIED':
					file_put_contents('C:/temp/'.$cargoPagado['estado'].'.json', json_encode($cargoPagado, JSON_PRETTY_PRINT));
					break;
			}

			// PETICION A LA API PARA CONCLUIR EL PAGO
			$urlApi = $this->config->rest->products->SIIE;
			$urlRoute = $this->config->rest->routes;
			$route = $urlRoute->cobranza_caja->paypal_response;
			$result = ApiREST::CurlRequest('POST', $urlApi, $route, $cargoPagado, false);
			if($result['status'] == 'success') {
				return true;
			}

			// si se recibio error o exception
			if($result['status'] == 'error' || $result['status'] == 'exception' || null == $result || false == $result) {
				$msg = $result['message'] == '' ? true : false;
				$result = FuncionesGenerales::error_handler($result, $msg);
			}
			return false;
		} catch (\Exception $e) {
			return false;
		}
	}
}