<?php

namespace Controllers;

class EstadisticasController extends ControllerBase {

	public function indexAction() {

	}

	public function obtenerAction() {
		if($this->request->isPost() && $this->request->isAjax()) {
			try {
				$query = "
					SELECT
						c.correo mejor_comprador,
						MAX(t.fechahora) ultimo_pago,
						COALESCE(comp.completos, 0) completos,
						COALESCE(pend.pendientes, 0) pendientes,
						COALESCE(dev.devoluciones, 0) devueltos,
						COUNT(t.subtotal) total_pagos,
						MIN(t.subtotal) pago_min,
						MAX(t.subtotal) pago_max,
						ROUND(AVG(t.subtotal), 2) gastos_prom,
						ROUND(STD(t.subtotal), 2) desvest_gastos,
						COALESCE((SUM(t.subtotal) - total_devuelto), SUM(t.subtotal)) total_gastado,
						COALESCE(dev.total_devuelto, 0) total_devuelto
					FROM transacciones t
					LEFT JOIN compradores c
						ON t.clave_comprador = c.clave
					LEFT JOIN (
						SELECT
							clave_comprador,
							COUNT(*) completos
						FROM transacciones
						WHERE id_estado = 1
							AND devuelto = 0
						GROUP BY clave_comprador
					) comp
						ON t.clave_comprador = comp.clave_comprador
					LEFT JOIN (
						SELECT
							clave_comprador,
							COUNT(*) pendientes
						FROM transacciones
						WHERE id_estado = 3
							AND devuelto = 0
						GROUP BY clave_comprador
					) pend
						ON t.clave_comprador = pend.clave_comprador
					LEFT JOIN (
						SELECT
							clave_comprador,
							SUM(devuelto) devoluciones,
							SUM(subtotal) total_devuelto
						FROM transacciones
						WHERE devuelto = 1
						GROUP BY clave_comprador
					) dev
						ON t.clave_comprador = dev.clave_comprador
					GROUP BY mejor_comprador
					HAVING total_pagos >= 1
					ORDER BY total_pagos DESC, total_gastado DESC;
				";
				$query = $this->db->query($query);
				$result = $query->fetchAll();
				if(!$result) throw new \Exception('No se encontro info de estadisticas', 404);

				$data = [];
				foreach($result as $i => $row) {
					$data[] = $row;
				}

				$this->response
					->setStatusCode(200)
					->sendHeaders()
					->setJsonContent([
						'status' => true,
						'code' => 200,
						'data' => $data
					], JSON_PRETTY_PRINT);
			} catch(\Exception $e) {
				$this->response
					->setStatusCode($e->getCode())->sendHeaders()
					->setJsonContent([
						'status' => false,
						'code' => $e->getCode(),
						'message' => $e->getMessage()
					], JSON_PRETTY_PRINT);
			}
		} else {
			$this->response->setStatusCode(405)->sendHeaders()->setJsonContent([
				'status' => false,
				'code' => 405,
				'message' => 'Metodo invalido'
			], JSON_PRETTY_PRINT);
		}
		return $this->response->send();
	}

}