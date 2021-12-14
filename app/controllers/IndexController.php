<?php

namespace Controllers;

use Models\Productos;

class IndexController extends ControllerBase {

	public function indexAction() {
		$productos = Productos::find()->toArray();
		$this->view->setVar('productos', $productos);
	}

	public function route404Action() {
		//$this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
		$this->view->setVar('title', '404');
		$this->view->setVar('descripcion', 'No Encontrado');
		$this->response->setStatusCode(404, 'Not Found')->sendHeaders();
		die('<h1>Not Found (404)</h1>');
	}
}