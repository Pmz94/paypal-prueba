<?php

//namespace Funciones;

class Funciones {

	/**
	 * Encode array from latin1 to utf8 recursively
	 * @param $dat
	 * @return array|string
	 */
	public function convertir_de_latin1_a_utf8_recursivo($dat) {
		if(is_string($dat)) {
			return utf8_encode($dat);
		} else if(is_array($dat)) {
			$ret = [];
			foreach($dat as $i => $d) $ret[$i] = self::convertir_de_latin1_a_utf8_recursivo($d);
			return $ret;
		} else if(is_object($dat)) {
			foreach($dat as $i => $d) $dat->$i = self::convertir_de_latin1_a_utf8_recursivo($d);
			return $dat;
		} else {
			return $dat;
		}
	}

	/**
	 * Obtener URL base
	 * @return string
	 */
	public function baseUrl() {
		return sprintf(
			"%s://%s%s",
			isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
			$_SERVER['SERVER_NAME'],
			$_SERVER['REQUEST_URI']
		);
	}

	/**
	 * Maneja los Exception que haya
	 * @param Exception $e
	 * @return array|string
	 */
	public function errorHandler($e) {
		$output = [
			'status' => false,
			'code' => $e->getCode(),
			'message' => $e->getMessage()
		];
		header('HTTP/1.1 400 Exception');
		return json_encode($output, JSON_PRETTY_PRINT);
	}

	/**
	 * En caso de que se haya hecho una peticion con el metodo no adecuado
	 * @return array|string
	 */
	public function nel() {
		$output = [
			'status' => false,
			'code' => 405,
			'message' => 'Nel'
		];
		header('HTTP/1.1 405 Method not allowed');
		return json_encode($output, JSON_PRETTY_PRINT);
	}
}