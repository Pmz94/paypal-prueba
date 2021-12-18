<?php

namespace Plugins;

use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Http\Response;
use Phalcon\Acl\Adapter\Memory as AclList;
use Phalcon\Acl;
use Phalcon\Acl\Resource as AclResource;
use Funciones\ApiREST;

class AclPlugin extends Plugin {

	public function beforeException(Event $event, Dispatcher $dispatcher, $exception) {
		// Handle ErrorExceptions
		if($exception instanceof \ErrorException || $exception instanceof \Exception) {
			$response = new \Phalcon\Http\Response();
			$response->setContent($exception->getMessage());
			$response->setStatusCode(404, 'Not Found');
			$response->send();
			exit;
		}

		// Handle 404 exceptions
		if($exception instanceof DispatchException) {
			$dispatcher->forward([
				'controller' => 'Index',
				'action' => 'route404'
			]);
			return false;
		}

		// Handle other exceptions
		$dispatcher->forward([
			'controller' => 'Index',
			'action' => 'route404'
		]);
		return false;
	}

	public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher) {
		$controller = $dispatcher->getControllerName();
		$action = $dispatcher->getActionName();

		$allowed = true;

		$LoginUser = $this->session->get('username');
		if($LoginUser) {
			//SI HAY SESIÓN, CONTINUAR CON LA CARGA DE LA RUTA
			$roleKey = $this->session->get('role_key');

			if(!$this->session->has('acl')) {
				$acl = $this->createACL($roleKey, $LoginUser);
				$this->session->set('acl', $acl);
			} else {
				$acl = $this->session->get('acl');
			}
			$allowed = $acl->isAllowed($roleKey, $controller, $action);
		}
		//$headers =  $this->request->getHeaders();
		if(!$allowed) {
			if($LoginUser) {
				if(
					$this->request->getHeader('x-load-module') == null
					||
					$this->request->getHeader('x-load-module') == ''
					||
					$this->request->getHeader('x-load-module') == false
				) {
					$response = new \Phalcon\Http\Response();
					$response->setContent('MAIN_ERROR_FORBIDDEN_REQUEST');
					$response->setStatusCode(403, 'Forbidden');
					$response->send();
					exit;
				} else {
					//Si no tiene acceso mostramos un mensaje y lo redireccionamos a la vista de error
					$dispatcher->forward([
						'controller' => 'Error',
						'action' => 'index',
						'params' => [
							'error_title' => 'MAIN_ERROR_FORBIDDEN',
							'error_message' => 'MAIN_ERROR_FORBIDDEN_MODULE',
							'controller' => $controller,
							'action' => $action
						]
					]);
					return false;
				}
			} else {
				//@todo se puede utilizar el componente de relogin en vez de sacarlo completamente en AJAX
				if($this->request->isAjax()) {
					$response = new Response();
					$response->setContent('MAIN_ERROR_NOT_SESSION');
					$response->setStatusCode(401, 'Unauthorized');
					$response->send();
					exit;
				} else {
					return $this->response->redirect('login');
				}
			}
		}
		return $allowed;
	}

	public function createACL($roleKey, $LoginUser): AclList {
		$urlApi = $this->getDI()->get('rest')->products->CEEDYE;
		$urlRoute = $this->getDI()->get('rest')->routes;

		$acl = new AclList();
		// Default action is deny access
		$acl->setDefaultAction(Acl::DENY);

		// Public area resources (frontend)
		$publicResources = [];
		foreach($publicResources as $resourceName => $actions) {
			$acl->addResource(new AclResource($resourceName), $actions);
		}

		// OBTENER LOS RECURSOS QUE SON PUBLICOS DE LA BASE DE DATOS
		$result = ApiREST::CurlRequest('GET', $urlApi, $urlRoute->aclresourcespublic->show);
		if($result['statusCode'] >= 200 && $result['statusCode'] < 400 && is_array($result['content']) && count($result['content']) > 0) {
			$publicResourcesBD = $result['content'];
			foreach($publicResourcesBD as $publicResourceBD) {
				$resource = $publicResourceBD['module'] . '/' . $publicResourceBD['controller'];
				$acl->addResource(new AclResource($resource), $publicResourceBD['action']);
			}
		}
		unset($result);

		// OBTENER LOS RECURSOS QUE SON PRIVADOS ES DECIR QUE REQUIEREN PRIVILEGIOS
		$result = ApiREST::CurlRequest('GET', $urlApi, $urlRoute->aclresources->show);
		if($result['statusCode'] >= 200 && $result['statusCode'] < 400 && is_array($result['content']) && count($result['content']) > 0) {
			$privateResources = $result['content'];
			foreach($privateResources as $privateResource) {
				$resource = $privateResource['module'] . '/' . $privateResource['controller'];
				$acl->addResource(new AclResource($resource), $privateResource['action']);
			}
		}
		unset($result);

		// AGREAGAMOS LOS TIPOS DE USUARIO PARA POSTERIORMENTE AGREGARLE LOS RECURSOS A LOS QUE PUEDE ACCEDER
		if($roleKey) {
			$result = ApiREST::CurlRequest('GET', $urlApi, $urlRoute->aclplugin->show, ['key' => $roleKey]);
			if($result['statusCode'] >= 200 && $result['statusCode'] < 400 && is_array($result['content']) && count($result['content']) > 0) {
				$roleInfo = $result['content'];
				foreach($roleInfo as $actualRole) {
					$acl->addRole($actualRole['key']);
					$resourcesRole = $actualRole['ResourcesRoles'];
					if($resourcesRole) {
						foreach($resourcesRole as $resourceData) {
							$resource = $resourceData['module'] . '/' . $resourceData['controller'];
							$acl->allow($actualRole['key'], $resource, $resourceData['action']);
						}
					}
					//AGREGAMOS LOS RECURSOS PUBLICOS QUE TIENE ACCESO EL USUARIO SOLO SE UTILIZARAN
					// SI EL USUARIO SE ENCUENTRA LOGUEADO
					foreach($publicResources as $resourceName => $actions) {
						$acl->allow($actualRole['key'], $resourceName, $actions);
					}

					foreach($publicResourcesBD as $publicResourceBD) {
						$resource = $publicResourceBD['module'] . '/' . $publicResourceBD['controller'];
						$acl->allow($actualRole['key'], $resource, $publicResourceBD['action']);
					}
				}
			}
			unset($result);

			if($acl->isRole($roleKey) && $LoginUser) {
				$result = ApiREST::CurlRequest('GET', $urlApi, $urlRoute->aclresourcesusers->show, ['users_username' => $LoginUser]);
				if($result['statusCode'] >= 200 && $result['statusCode'] < 400 && is_array($result['content']) && count($result['content']) > 0) {
					$resourcesUser = $result['content'];
					foreach($resourcesUser as $resourceData) {
						$resource = $resourceData['module'] . '/' . $resourceData['controller'];
						if($resourceData['access_granted'] == 0) {
							$acl->deny($roleKey, $resource, $resourceData['action']);
						} else {
							$acl->allow($roleKey, $resource, $resourceData['action']);
						}
					}
				}
			}
			unset($result);
		}
		return $acl;
	}
}