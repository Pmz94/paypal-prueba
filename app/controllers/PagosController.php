<?php

namespace Controllers;

use Models\Productos;
use PayPal\Api\Payment;
use PayPal\Api\Amount;
use PayPal\Api\RefundRequest;
use PayPal\Api\Sale;
use PayPal\Api\Payer;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Exception\PayPalConnectionException as PPException;

class PagosController extends ControllerBase {

	public function indexAction() {

	}

	public function obtenerPagosAction() {
		if($this->request->isPost() && $this->request->isAjax()) {
			try {
				$query = "
                    SELECT
                        t.id,
                        t.id_transaccion,
                        t.id_venta,
                        t.fechahora,
                        p.nombre producto,
                        COALESCE((t.subtotal / t.cantidad), 0) AS precio,
                        COALESCE(t.cantidad, 0) AS cantidad,
                        t.subtotal,
                        CONCAT(c.nombre, ' ', c.apellido) comprador,
                        c.correo,
                        e.estado,
                        t.devuelto
                    FROM transacciones t
                    LEFT JOIN compradores c
                        ON t.clave_comprador = c.clave
                    LEFT JOIN productos p
                        ON t.id_producto = p.id
                    LEFT JOIN estadosdepago e
                        ON t.id_estado = e.id
					ORDER BY t.fechahora DESC
				";

				$querycount = "
					SELECT COUNT(*) count
				    FROM (${query}) t
				";
				$querycount = $this->db->query($querycount);

				$count = 0;
				foreach($querycount->fetchAll() as $i => $row) {
					$count = intval($row['count']);
				}

				if($count <= 0) throw new \Exception('No hay datos', 404);

				$query = $this->db->query($query);
				$result = $query->fetchAll();
				if(!$result) throw new \Exception('No hay transacciones', 404);

				$data = [];
				foreach($result as $row) {
					$data[] = $row;
				}

				$this->response->setStatusCode(200)->sendHeaders()->setJsonContent([
					'status' => true,
					'code' => 200,
					'count' => $count,
					'data' => $data
				], JSON_PRETTY_PRINT);
			} catch(\Exception $e) {
				$this->response->setStatusCode($e->getCode())->sendHeaders()->setJsonContent([
					'status' => false,
					'code' => $e->getCode(),
					'message' => $e->getMessage()
				], JSON_PRETTY_PRINT);
			}
		} else {
			$this->response->setStatusCode(405)->sendHeaders()->setJsonContent([
				'status' => false,
				'code' => 405,
				'message' => 'Nel'
			], JSON_PRETTY_PRINT);
		}
		return $this->response->send();
	}

	public function crearAction() {
		$this->view->disable();
		try {
			$id_producto = $this->request->getPost('id_producto', 'int!');
			$cantidad = $this->request->getPost('cantidad', 'int!');

			if(!$id_producto && !$cantidad) {
				throw new \Exception('Llene todos los campos', 422);
			}

			$producto = Productos::find([
				'id = :id:',
				'bind' => ['id' => $id_producto]
			])->toArray();

			$precio = $producto[0]['precio'];
			$nombre = $producto[0]['nombre'];

			$total = $precio * $cantidad;

			$payer = new Payer();
			$payer->setPaymentMethod('paypal');

			$item = new Item();
			$item->setSku($id_producto)->setName($nombre)->setQuantity($cantidad)->setPrice($precio)->setCurrency('MXN');

			$itemlist = new ItemList();
			$itemlist->setItems([$item]);

			$amount = new Amount();
			$amount->setTotal($total)->setCurrency('MXN');

			$transaction = new Transaction();
			$transaction->setItemList($itemlist)->setAmount($amount)->setDescription('Pagando un producto');

			$redirectUrls = new RedirectUrls();
			$redirectUrls->setReturnUrl($this->url->get('/Pago?status=true'))->setCancelUrl($this->url->getBaseUri());

			$payment = new Payment();
			$payment->setIntent('sale')->setPayer($payer)->setTransactions([$transaction])->setRedirectUrls($redirectUrls);

			$payment->create($this->paypal);

			$this->response->setStatusCode(200)->sendHeaders();
			$this->response->setJsonContent([
				'status' => true,
				'code' => 200,
				'url' => $payment->getApprovalLink(),
				'payment' => $payment->toArray()
			]);
		} catch(\Exception $e) {
			$this->response->setStatusCode($e->getCode())->sendHeaders();
			$this->response->setJsonContent([
				'status' => false,
				'code' => $e->getCode(),
				'message' => $e->getMessage()
			]);
		}
		return $this->response->send();
	}

	public function verPagoAction() {
		if($this->request->isPost() && $this->request->isAjax()) {
			try {
				$id_transaccion = $this->request->getPost('id_transaccion');

				$query = "
					SELECT *
                    FROM transacciones t
                    LEFT JOIN compradores c
                        ON t.clave_comprador = c.clave
                    WHERE t.id_transaccion = :id_transaccion
                    LIMIT 1
				";
				$values = ['id_transaccion' => $id_transaccion];
				$dataType = ['id_transaccion' => \Phalcon\Db\Column::BIND_PARAM_STR];

				$result = $this->db->query($query, $values, $dataType);
				$pago = $result->fetchAll();

				$apiContext = $this->paypal;

				$payment = Payment::get($id_transaccion, $apiContext);

				$output = [];
				foreach($pago as $row) {
					$output['id_transaccion'] = $payment->id;
					$output['id_venta'] = $payment->transactions[0]->related_resources[0]->sale->id;
					$output['correo'] = $payment->payer->payer_info->email;
					$output['id_producto'] = intval($payment->transactions[0]->item_list->items[0]->sku);
					$output['producto'] = $payment->transactions[0]->item_list->items[0]->name;
					$output['precio'] = intval($payment->transactions[0]->item_list->items[0]->price);
					$output['cantidad'] = intval($payment->transactions[0]->item_list->items[0]->quantity);
					$output['subtotal'] = intval($payment->transactions[0]->amount->details->subtotal);
					$output['envio'] = intval($payment->transactions[0]->amount->details->shipping);
					$output['total'] = intval($payment->transactions[0]->amount->total);
					$output['fecha'] = date('d/m/Y', strtotime($row['fechahora']));
					$output['hora'] = date('h:ia', strtotime($row['fechahora']));
					$output['estado'] = $payment->transactions[0]->related_resources[0]->sale->state;
					$output['devuelto'] = !!($payment->transactions[0]->related_resources[0]->sale->state === 'refunded');
					$output['fechaDev'] = ($row['fechahora_cancelado']) ? date('d/m/Y', strtotime($row['fechahora_cancelado'])) : '';
					$output['horaDev'] = ($row['fechahora_cancelado']) ? date('h:ia', strtotime($row['fechahora_cancelado'])) : '';
				}

				$this->response->setStatusCode(200)->sendHeaders()->setJsonContent([
					'status' => true,
					'code' => 200,
					'data' => $output
				], JSON_PRETTY_PRINT);
			} catch(\Exception $e) {
				$this->response->setStatusCode(400)->sendHeaders()->setJsonContent([
					'status' => false,
					'code' => $e->getCode(),
					'message' => $e->getMessage()
				], JSON_PRETTY_PRINT);
			}
		} else {
			$this->response->setStatusCode(405)->sendHeaders()->setJsonContent([
				'status' => false,
				'code' => 405,
				'message' => 'Nel'
			], JSON_PRETTY_PRINT);
		}
		return $this->response->send();
	}

	public function cancelarPagoAction() {
		if($this->request->isPost() && $this->request->isAjax()) {
			try {
				$saleId = ($this->request->hasPost('id_venta')) ? $this->request->getPost('id_venta') : null;

				if(!$saleId) throw new \Exception('No ID', 404);

				$apiContext = $this->paypal;

				$sale = Sale::get($saleId, $apiContext);

				$currency = $sale->amount->currency;
				$total = $sale->amount->total;

				$amount = new Amount();
				$amount->setCurrency($currency)->setTotal($total);

				$refundRequest = new RefundRequest();
				$refundRequest->setAmount($amount);

				$sale = new Sale();
				$sale->setId($saleId);

				$refundedSale = $sale->refundSale($refundRequest, $apiContext);

				if($refundedSale) {
					$query = "
						UPDATE transacciones
                        SET id_estado = 4,
                            fechahoraAct = NOW(),
                            devuelto = 1,
                            fechahora_cancelado = NOW()
                        WHERE id_venta = :id_venta
					";

					$values = ['id_venta' => $saleId];

					$dataTypes = ['id_venta' => \Phalcon\Db\Column::BIND_PARAM_STR];

					$result = $this->db->execute($query, $values, $dataTypes);

					if($result) {
						$this->response->setStatusCode(200)->sendHeaders()->setJsonContent([
							'status' => true,
							'code' => 200,
							'message' => 'Transaccion cancelada'
						], JSON_PRETTY_PRINT);
					} else throw new \Exception('Error al guardar registro en BD', 500);
				} else throw new \Exception('Error al procesar reembolso', 500);

			} catch(PPException $e) {
				$data = json_decode($e->getData(), true);
				$message = $data['message'] . ' ' . $data['information_link'];

				$this->response->setStatusCode(400)->sendHeaders()->setJsonContent([
					'status' => false,
					'code' => $e->getCode(),
					'message' => $message
				], JSON_PRETTY_PRINT);
			} catch(\Exception $e) {
				$this->response->setStatusCode(400)->sendHeaders()->setJsonContent([
					'status' => false,
					'code' => $e->getCode(),
					'message' => $e->getMessage()
				], JSON_PRETTY_PRINT);
			}
		} else {
			$this->response->setStatusCode(405)->sendHeaders()->setJsonContent([
				'status' => false,
				'code' => 405,
				'message' => 'Nel'
			], JSON_PRETTY_PRINT);
		}
		return $this->response->send();
	}
}