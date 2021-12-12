<?php

namespace Funciones;

use Phalcon\DI\FactoryDefault;
use Phalcon\Exception;
use Common\FuncionesGenerales;

class ApiREST {

	private static $configREST;

	private static $errorResponse;

	public static $responseRaw;

	public static $responseStatusCode;

	/**
	 * Funcion CURL Core, hace una peticion CURL directo a la API
	 * @param $method                   string  (GET, POST, PUT, DELETE)
	 * @param $url                      string  url
	 * @param null $params array   parametros
	 * @param bool|false $files bool    Si dentro de los parametos incluye una ruta de un archivo a leer
	 * @return bool|mixed|string
	 */
	public static function CurlCore($method = null, $url = null, $params = null, $files = false) {
		// VALIDAMOS METODO RECIBIDO
		if($method !== null && !in_array(strtoupper($method), array('GET', 'POST', 'PUT', 'DELETE', 'HEAD'))) {
			return ['statusCode' => 405, 'content' => ['code' => -1]];
		}
		$method = strtoupper($method);

		// VALIDAMOS SI RECIBIMOS EL URL
		if($url === null) return ['statusCode' => 400, 'content' => ['code' => -1]];

		try {
			// Por si no se especifica el usuario que se realizo la peticion, se adjunta el username_request, algunas routes la usan
			$session_di = FuncionesGenerales::getSession();
			if($session_di) {
				$username_request = $session_di->get('username');
				$bfp = $session_di->get('bfp');
				$ip = FuncionesGenerales::get_client_ip();
				if(!is_array($params) && ($username_request || $bfp || $ip)) $params = array();
				if($username_request) $params['username_request'] = $username_request;
				if($bfp) $params['bfp'] = $bfp;
				if($ip) $params['ip'] = $ip;
			}

			// not array and not object, make it an array
			if(!is_array($params) && !is_object($params)) $params = (array)$params;

			// Se inicializa el CURL
			$ch = curl_init();

			// Dependiendo del metodo recibido se envian los parametros
			switch($method) {
				case 'GET':
					if(count($params) > 0) {
						$url .= '?' . http_build_query($params);
					}
					break;
				case 'POST':
					if($files === false) {
						$params = http_build_query($params);
					}
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
					curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
					break;
				case 'PUT':
					$params = http_build_query($params);
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
					curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
					break;
				case 'DELETE':
					$params = http_build_query($params);
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
					curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
					break;
				default:
					break;
			}
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FAILONERROR, true);

			// ENVIAMOS Y RECIBIMOS LA PETICION
			$response = curl_exec($ch);
			if($response === false) {
				return ['statusCode' => 409, 'content' => ['code' => -1, 'message' => curl_error($ch)]];
			} else {
				$statusCode = filter_var(curl_getinfo($ch, CURLINFO_HTTP_CODE), FILTER_VALIDATE_INT);
				$content = substr($response, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
				$content = (self::isJson($content) == false) ? $content : json_decode($content, true);
			}
			curl_close($ch);
			return ['statusCode' => $statusCode, 'content' => $content];
		} catch(\Exception $ex) {
			return ['statusCode' => $ex->getCode(), 'content' => ['code' => -1, 'message' => $ex->getMessage()]];
		}
	}

	/**
	 * Funcion CURL REQUEST, hace una peticion CURL a traves de la funcion CurlCore y regresa un ARRAY
	 * @param $method                   string  (GET, POST, PUT, DELETE)
	 * @param $urlProductName           string  API o portal
	 * @param $modelPath                string  Route a utilizar
	 * @param null $params array   parametros
	 * @param $controlador              string  Controlador quien realizo la petición y asi obtener el diccionario
	 *                                          correspondiente
	 * @param bool|false $files bool    Si dentro de los parametos incluye una ruta de un archivo a leer
	 * @return bool|mixed|string
	 */
	public static function CurlRequest($method, $urlProductName = null, $modelPath = null, $params = null, $controlador = null, $files = false) {
		try {
			// VALIDAMOS SI RECIBIMOS EL URL
			if($urlProductName === null || $modelPath === null) {
				return ['statusCode' => 400, 'content' => ['code' => -1]];
			}
			// OBTENEMOS LA URL DE LA API    =>    http://localhost/techba/api/
			$url = rtrim($urlProductName, "/") . "/";
			// URL A CONECTAR  =>   escuelas/count   =>  http://localhost/techba/api/model/escuelas/count
			$url .= ltrim($modelPath, "/");
			return self::CurlCore($method, $url, $params, $files);
		} catch(\Exception $ex) {
			return ['statusCode' => $ex->getCode(), 'content' => ['code' => -1, 'message' => $ex->getMessage()]];
		}
	}

	/**
	 * Funcion CURL REQUEST RESPONSE, hace una peticion CURL a traves de la funcion CurlCore y regresa un RESPONSE para
	 *      una llamada AJAX
	 * @param $method                   string  (GET, POST, PUT, DELETE)
	 * @param $urlProductName           string  API
	 * @param $modelPath                string  Route a utilizar
	 * @param null $params array   parametros
	 * @param $controlador              string  Controlador quien realizo la petición y asi obtener el diccionario
	 *                                          correspondiente
	 * @param bool|false $files bool    Si dentro de los parametos incluye una ruta de un archivo a leer
	 * @return bool|mixed|string
	 */
	public static function CurlRequestResponse($method, $urlProductName = null, $modelPath = null, $params = null, $controlador = null, $files = false) {
		$responseToSend = new \Phalcon\Http\Response();
		try {
			// VALIDAMOS SI RECIBIMOS EL URL
			if($urlProductName === null || $modelPath === null) {
				return ['statusCode' => 400, 'content' => ['code' => -1]];
			}
			// OBTENEMOS LA URL DE LA API    =>    http://localhost/techba/api/
			$url = rtrim($urlProductName, "/") . "/";
			// URL A CONECTAR  =>   escuelas/count   =>  http://localhost/techba/api/model/escuelas/count
			$url .= ltrim($modelPath, "/");
			$respuesta = self::CurlCore($method, $url, $params, $files);
			$statusCode = $respuesta['statusCode'];
			$content = $respuesta['content'];

			$responseToSend->setStatusCode($statusCode, FuncionesGenerales::getHTMLStatusCodeName($statusCode));
			switch($respuesta['statusCode']) {
				case 200: // OK
					$responseToSend->setJsonContent($content);
					break;
				case 201:
				case 204:
					break;
				case $respuesta['statusCode'] >= 400:
					$responseToSend->setJsonContent(FuncionesGenerales::error_handler($content, $controlador, $statusCode));
					break;
				default:
					break;
			}
			return $responseToSend;
		} catch(\Exception $ex) {
			$responseToSend->setStatusCode($ex->getCode());
			$responseToSend->setJsonContent($ex->getMessage());
			return $responseToSend;
		}
	}

	/*
	 * @return boolean
	 */
	private static function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

	/*
	 * Arreglo con los parametros a enviar en cada petición a la API
	 *
	 * Nota sobre las "Keys":
	 * 1) NO pueden llevar "_"
	 * 2) "-" será reemplazado por "_"
	 * 3) Se convertirá a mayusculas
	 */
	private static function getHeaders(array $data, $modelPath) {
		if(array_key_exists("query", $data)) {
			$data = $data["query"];
		}
		if(array_key_exists("body", $data)) {
			$data = $data["body"];
		}

		// TIME
		$time = time();

		//  TIME  +  CLIENT_ID  +  DATA  +  PATH
		// 1413487950  +  1  +  offset=0&order=claveescuela&limit=10&page=1  +  model/escuelas/show
		$message = $time . ClientConfig::getId() . http_build_query($data) . $modelPath;

		if(false) {
			var_dump([
				"time" => $time,
				"clientid" => ClientConfig::getId(),
				"build_query" => http_build_query($data),
				"modelPath" => $modelPath,
				"privateKey" => ClientConfig::getPrivateKey(),
				"message" => $message,
				"hash" => hash_hmac('sha256', $message, ClientConfig::getPrivateKey())
			]);
		}

		return array(
			"API-ID" => ClientConfig::getId(),
			"API-HASH" => hash_hmac('sha256', $message, ClientConfig::getPrivateKey()),
			"API-TIME" => $time
		);
	}
}