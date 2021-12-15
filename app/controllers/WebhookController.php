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

	public $apiContext;
	public $webhookId;

	public function initialize() {
		$this->apiContext = $this->paypal;
		$this->webhookId = $this->config->paypal_credentials->webhook_id;
	}

	public function indexAction() {
		// Obtener parametros para verificar webhook
		$json = file_get_contents('php://input');
		$headers = $this->request->getHeaders();
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
			// Obtener body y headers
			$headers = $this->request->getHeaders();
			$json = file_get_contents('php://input');

			// Validar headers necesarios
			$headers = array_change_key_case($headers, CASE_UPPER);
			$headersRequired = [
				'PAYPAL-AUTH-ALGO',
				'PAYPAL-TRANSMISSION-ID',
				'PAYPAL-CERT-URL',
				'PAYPAL-TRANSMISSION-SIG',
				'PAYPAL-TRANSMISSION-TIME'
			];
			foreach($headersRequired as $headerNeeded) {
				if(!isset($headers[$headerNeeded])) {
					throw new \Exception("Falta el header ${headerNeeded}", 400);
				}
			}

			// Verificar webhook
			$signatureVerification = new VerifyWebhookSignature();
			$signatureVerification->setAuthAlgo($headers['PAYPAL-AUTH-ALGO']);
			$signatureVerification->setTransmissionId($headers['PAYPAL-TRANSMISSION-ID']);
			$signatureVerification->setCertUrl($headers['PAYPAL-CERT-URL']);
			$signatureVerification->setWebhookId($this->webhookId);
			$signatureVerification->setTransmissionSig($headers['PAYPAL-TRANSMISSION-SIG']);
			$signatureVerification->setTransmissionTime($headers['PAYPAL-TRANSMISSION-TIME']);
			$signatureVerification->setRequestBody($json);
			$output = $signatureVerification->post($this->apiContext);
			$verificationStatus = $output->getVerificationStatus();

			// En caso de que no sea autentico
			if(strtoupper($verificationStatus) === 'FAILURE') {
				throw new \Exception('Webhook invalido', 400);
			}

			$webhook_event = json_decode($json);
			$evento = $webhook_event->event_type;
			$idTransaccion = $webhook_event->resource->parent_payment;
			date_default_timezone_set('America/Hermosillo');

			// Obtener el json de la transaccion que se hizo
			$payment = Payment::get($idTransaccion, $this->apiContext);

			// Arreglo con parametros para BD
			$paymentArr = json_decode($payment, false);
			$cargoPagado = [];
			$cargoPagado['idTransaccion'] = $idTransaccion; //ID de transaccion
			$cargoPagado['idVenta'] = $webhook_event->resource->id; //ID de venta
			$cargoPagado['productos'] = $paymentArr->transactions[0]->item_list->items; //Arreglo de productos
			$cargoPagado['total'] = $webhook_event->resource->amount->total; //Total que se pago
			$cargoPagado['comision'] = $webhook_event->resource->transaction_fee->value; //Comision que cobra paypal
			$cargoPagado['estado'] = $webhook_event->resource->state; //Estado de pago
			$cargoPagado['fechahora'] = date('Y-m-d H:i:s'); //hora actual
			$cargoPagado['horaPaypal'] = $webhook_event->resource->update_time; //hora de paypal de la Venta
			$cargoPagado['evento'] = $evento;

			// Realizar accion dependiendo del evento que haya tenido el pago
			switch($evento) {
				case 'PAYMENT.SALE.PENDING':
				case 'PAYMENT.SALE.DENIED':
				case 'PAYMENT.SALE.COMPLETED':
					break;
				case 'PAYMENT.SALE.REFUNDED':
				case 'PAYMENT.SALE.REVERSED':
					$cargoPagado['idDevolucion'] = $webhook_event->resource->id; //ID de devolucion
					$cargoPagado['idVenta'] = $webhook_event->resource->sale_id; //ID de venta
					break;
			}

			file_put_contents('./tmp/' . $cargoPagado['estado'] . '.json', json_encode($cargoPagado, JSON_PRETTY_PRINT));

			// Agregar evento de webhook a la BD
			$query = "
                INSERT INTO webhooks(id_webhook, fechahora, tipo_evento, id_transaccion)
				VALUES (
					:id_webhook,
					NOW(),
					:id_venta,
					:id_transaccion
				);
			";
			$values = [
				'id_webhook' => $webhook_event->id,
				'id_venta' => $webhook_event->resource->id,
				'id_transaccion' => $idTransaccion
			];
			$this->db->execute($query, $values);

			$this->response->setStatusCode(200, 'OK')->sendHeaders();
			$this->response->setJsonContent([
				'status' => true,
				'code' => 200,
				'message' => 'Webhook recibido'
			], JSON_PRETTY_PRINT);
		} catch(\Exception $e) {
			$this->response->setStatusCode($e->getCode())->sendHeaders();
			$this->response->setJsonContent([
				'status' => false,
				'code' => $e->getCode(),
				'message' => $e->getMessage()
			], JSON_PRETTY_PRINT);
		}
		return $this->response->send();
	}
}