<?php

namespace Models;

class Productos extends \Phalcon\Mvc\Model {

	public $id;

	public $nombre;

	public $precio;

	public function initialize() {
		$this->setConnectionService('db');
		$this->setSource('productos');
	}
}