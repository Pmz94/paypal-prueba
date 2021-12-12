<?php

namespace Controllers;

use Models\Productos;

class IndexController extends ControllerBase {

	public function indexAction() {
		$productos = Productos::find()->toArray();
		$this->view->setVar('productos', $productos);

		if($this->request->isAjax() && $this->request->isPost()) {
			$cmd = strtolower($this->request->getPost('cmd'));
			if($cmd == 'peticion') {
				return $this->peticion();
			}
		}
	}

	public function peticion() {
		try {
			$query = "SELECT nombre FROM productos ORDER BY RAND() LIMIT 1";
			$result = $this->db->query($query);
			$result->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
			$row = [];
			if($result) while($data = $result->fetch()) $row = $data['nombre'];

			$this->response->setStatusCode(200, 'OK')->sendHeaders();
			$this->response->setJsonContent([
				'status' => true,
				'code' => 200,
				'message' => $row
			], JSON_PRETTY_PRINT);
		} catch(\Exception $e) {
			$this->response->setStatusCode($e->getCode(), 'Exception')->sendHeaders();
			$this->response->setJsonContent([
				'message' => $e->getMessage()
			], JSON_PRETTY_PRINT);
		}
		return $this->response->send();
	}

	public function route404Action() {
		//$this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
		$this->view->setVar('title', '404');
		$this->view->setVar('descripcion', 'No Encontrado');
		$this->response->setStatusCode(404, 'Not Found')->sendHeaders();
		die('<h1>Not Found (404)</h1>');
	}
}