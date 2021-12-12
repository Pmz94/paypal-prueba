<?php

namespace Controllers;

use Models\Productos;

class ProductosController extends ControllerBase {

	public function indexAction() {
		if($this->request->isAjax() && $this->request->isPost()) {
			switch(strtolower($this->request->getPost('cmd'))) {
				case 'ver':
					return $this->ver();
				case 'crear':
					return $this->crear();
				case 'editar':
					return $this->editar();
				case 'borrar':
					return $this->borrar();
			}
		}
	}

	public function ver() {
		try {
			$productos = Productos::find()->toArray();

			$this->response->setStatusCode(200, 'OK')->sendHeaders();
			$this->response->setJsonContent([
				'status' => true,
				'code' => 200,
				'data' => $productos
			], JSON_PRETTY_PRINT);
		} catch(\Exception $e) {
			$this->response->setStatusCode(400, 'Exception')->sendHeaders();
			$this->response->setJsonContent([
				'status' => false,
				'code' => $e->getCode(),
				'message' => $e->getMessage()
			], JSON_PRETTY_PRINT);
		}
		return $this->response->send();
	}

	public function crear() {
		try {
			$nombre = trim($this->request->getPost('nombre')) ?? null;
			$precio = ($this->request->getPost('precio') > 0) ? $this->request->getPost('precio') : null;

			$query = "
				INSERT INTO productos(nombre, precio)
				VALUES
					(:nombre, :precio)
			";
			$values = [
				'nombre' => $nombre,
				'precio' => $precio
			];
			$datatypes = [
				'nombre' => \Phalcon\Db\Column::BIND_PARAM_STR,
				'precio' => \Phalcon\Db\Column::BIND_PARAM_INT
			];

			$result = $this->db->execute($query, $values, $datatypes);
			if($result) $data = 'Producto agregado';

			$this->response->setStatusCode(200, 'OK')->sendHeaders();
			$this->response->setJsonContent([
				'status' => true,
				'code' => 200,
				'message' => $data
			], JSON_PRETTY_PRINT);
			return $this->response->send();

		} catch(\Exception $e) {
			$this->response->setStatusCode(400, 'Exception')->sendHeaders();
			$this->response->setJsonContent([
				'status' => false,
				'code' => $e->getCode(),
				'message' => $e->getMessage()
			], JSON_PRETTY_PRINT);
			return $this->response->send();
		}
	}

	public function editar() {
		try {
			$id = $this->request->getPost('id', 'int!') ?? null;
			$nombre = trim($this->request->getPost('nombre')) ?? null;
			$precio = ($this->request->getPost('precio') > 0) ? $this->request->getPost('precio') : null;

			$query = "
				UPDATE productos
				SET nombre = :nombre AND precio = :precio
				WHERE id = :id
			";
			$values = [
				'id' => $id,
				'nombre' => $nombre,
				'precio' => $precio
			];
			$datatypes = [
				'id' => \Phalcon\Db\Column::BIND_PARAM_INT,
				'nombre' => \Phalcon\Db\Column::BIND_PARAM_STR,
				'precio' => \Phalcon\Db\Column::BIND_PARAM_INT
			];

			$result = $this->db->execute($query, $values, $datatypes);
			if($result) $data = 'Producto modificado';

			$this->response->setStatusCode(200, 'OK')->sendHeaders();
			$this->response->setJsonContent([
				'status' => true,
				'code' => 200,
				'message' => $data
			], JSON_PRETTY_PRINT);
			return $this->response->send();

		} catch(\Exception $e) {
			$this->response->setStatusCode(400, 'Exception')->sendHeaders();
			$this->response->setJsonContent([
				'status' => false,
				'code' => $e->getCode(),
				'message' => $e->getMessage()
			], JSON_PRETTY_PRINT);
			return $this->response->send();
		}
	}

	public function borrar() {
		try {
			$id_producto = trim($this->request->getPost('id_producto')) ?? null;

			$query = "DELETE FROM productos WHERE id = ?";
			$values = [$id_producto];

			$result = $this->db->execute($query, $values);
			if($result) $data = 'Producto borrado';

			$this->response->setStatusCode(200, 'OK')->sendHeaders();
			$this->response->setJsonContent([
				'status' => true,
				'code' => 200,
				'message' => $data
			], JSON_PRETTY_PRINT);
			return $this->response->send();

		} catch(\Exception $e) {
			$this->response->setStatusCode(400, 'Exception')->sendHeaders();
			$this->response->setJsonContent([
				'status' => false,
				'code' => $e->getCode(),
				'message' => $e->getMessage()
			], JSON_PRETTY_PRINT);
			return $this->response->send();
		}
	}

}