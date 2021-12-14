<?php
/**
 * Created by PhpStorm.
 * User: Pedro Muñoz
 * Date: 14/12/21
 * Time: 12:38
 */

namespace Controllers;

use PayPal\Api\Payment;
use PayPal\Api\VerifyWebhookSignature;

class WebhookController extends ControllerBase {

	public function indexAction() {
		$json = file_get_contents('php://input');
		$webhook = json_decode($json);

		$this->response->setStatusCode(200, 'OK')->sendHeaders();
		$this->response->setJsonContent([
			'status' => true,
			'code' => 200,
			'message' => 'Webhook recibido',
			'webhook' => $webhook ?: []
		], JSON_PRETTY_PRINT);
		return $this->response->send();
	}

	private function webhook() {
		try {
			$json = file_get_contents('php://input');

			$apiContext = $this->paypal;
			$webhookId = $this->config->paypal_credentials->webhook_id;

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

			// Verificar webhook
			$output = $signatureVerification->post($apiContext);

			// En caso de que no sea autentico
			if(strtoupper($output->getVerificationStatus()) === 'FAILURE') {
				throw new \Exception('Webhook invalido', 400);
			}

			$webhook = json_decode($json);
			date_default_timezone_set('America/Hermosillo');
			$evento = $webhook->event_type;
			$idTransaccion = $webhook->resource->parent_payment;

			// Obtener el json de la transaccion que se hizo
			$payment = Payment::get($idTransaccion, $apiContext);

			// Arreglo con parametros para BD
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

			// Realizar accion dependiendo del evento que haya tenido el pago
			switch($evento) {
				case 'PAYMENT.SALE.COMPLETED':
					break;
				case 'PAYMENT.SALE.PENDING':
					break;
				case 'PAYMENT.SALE.DENIED':
					break;
				case 'PAYMENT.SALE.REFUNDED' || 'PAYMENT.SALE.REVERSED':
					$cargoPagado['idDevolucion'] = $webhook->resource->id; //ID de devolucion
					$cargoPagado['idVenta'] = $webhook->resource->sale_id; //ID de venta
					break;
			}

			file_put_contents('./tmp/' . $cargoPagado['estado'] . '.json', json_encode($cargoPagado, JSON_PRETTY_PRINT));

			// PETICION A LA API PARA CONCLUIR EL PAGO

			$this->response->setStatusCode(200, 'OK')->sendHeaders();
			$this->response->setJsonContent([
				'status' => true,
				'code' => 200,
				'message' => 'Webhook recibido'
			], JSON_PRETTY_PRINT);
		} catch(\Exception $e) {
			$this->response->setStatusCode($e->getCode(), 'Exception')->sendHeaders();
			$this->response->setJsonContent([
				'status' => false,
				'code' => $e->getCode(),
				'message' => $e->getMessage()
			], JSON_PRETTY_PRINT);
		}
		return $this->response->send();
	}
}