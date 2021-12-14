<?php

namespace Controllers;

use Phalcon\Mvc\Controller;

class ControllerBase extends Controller {

	public function status($code) {
		$this->response->setStatusCode($code)->sendHeaders();
		return $this;
	}

	public function send($content) {
		if(!$this->response->getStatusCode()) {
			$this->response->setStatusCode(200)->sendHeaders();
		}
		if(is_array($content)) {
			$this->response->setJsonContent($content, JSON_PRETTY_PRINT);
		} else {
			$this->response->setContent($content);
		}
		return $this->response->send();
	}
}