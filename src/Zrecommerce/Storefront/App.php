<?php
/**
 * The core of the MVC, this class handles MVC dispatching, among other things.
 * Yes, you can run this on nginx right out of the box.
 * 
 * See http:/www.zrecommerce.com/spex/mvc for documentation.
 * 
 * @author ZRECommerce LLC
 */
// nginx support BOD
if (!function_exists('apache_request_headers')) {

	function apache_request_headers() {
		foreach ($_SERVER as $key => $value) {
			if (substr($key, 0, 5) == "HTTP_") {
				$key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
				$out[$key] = $value;
			} else {
				$out[$key] = $value;
			}
		}
		return $out;
	}

}

if (!function_exists('getallheaders')) {

	function getallheaders() {
		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		return $headers;
	}

}

// nginx support EOD

namespace Zrecommerce\Storefront;

class App {

	static function getParam($name, $val = null) {
		return isset($_REQUEST[$name]) ? $_REQUEST[$name] : $val;
	}

	static function getNamespace($name) {
		if (empty($_SESSION[$name]))
			$_SESSION[$name] = array();

		return $_SESSION[$name];
	}

	static function setNamespace($name, $values) {
		$_SESSION[$name] = $values;
	}

	static function getSettings($forceReload = false, $saveToSession = true) {
		$settings = null;

		if ($forceReload || empty($_SESSION['application_settings'])) {

			$ini_file = realpath(APP_PATH . '/config/settings.ini');
			$ini_key = (APP_ENV != 'production' ? APP_ENV . ' : production' : APP_ENV);

			$app_ini = parse_ini_file($ini_file, true);

			$settings = $app_ini['production'];

			if ($ini_key != 'production') {
				$env_settings = $app_ini[$ini_key];
				$settings = array_merge($settings, $env_settings);
			}

			if ($saveToSession) {
				$_SESSION['application_settings'] = $settings;
			}
		} else {
			$settings = $_SESSION['application_settings'];
		}

		return $settings;
	}

	static function getMVC() {
		$uri = $_SERVER['REQUEST_URI'];
		$path = preg_replace('/\?.*/', '', $uri);
		$query = str_replace($path, '', preg_replace('/^.*(\?)/', '', $uri));
		$mvc = array_filter(explode('/', $path));
		
		$mvc_len = count($mvc);
		
		parse_str($_SERVER['QUERY_STRING'], $params);
		parse_str($query, $moreParams);
		
		$params = array_merge($params, $moreParams);
		
		if ($mvc_len > 0 && is_numeric($mvc[$mvc_len]) ) {
			$params['id'] = $mvc[$mvc_len];
			$mvc[$mvc_len] = 'index';
		}
		
		if ($mvc_len < 3) {
			for ($i = 0; $i < (3 - $mvc_len); $i++) {
				if ($mvc_len < 2) {
					array_unshift($mvc, 'index');
				} else {
					array_push($mvc, 'index');
				}
			}
		}
		// Assert our array index starts at zero.
		$max = count($mvc) - 1;
		$newKeys = range(0, $max);

		$mvc = array_combine($newKeys, $mvc);
		
		if (!function_exists('arrayWalkCallbackGetMvc')) {

			function arrayWalkCallbackGetMvc(&$var, $key) {
				$var = preg_replace('/\?.*/', '', $var);
			}

		}
		array_walk($mvc, 'arrayWalkCallbackGetMvc');

		foreach ($mvc as $i => $part) {
			if ($i < 2 && is_numeric($part)) {
				$mvc[$i] = '';
			} else if ($i == 3 && is_numeric($part)) {
				$mvc[$i] = '';
				if (!isset($params['id']) || !isset($_REQUEST['id'])) {
					$params['id'] = $part;
					$_REQUEST['id'] = $part;
				}
			} else if ($i > 3) {
				// Ignore.
			}
		}
		
		$mvc['params'] = array_merge($_REQUEST, $params);
		$mvc['uri'] = $uri;

		return $mvc;
	}

	static function getModule() {
		$mvc = self::getMVC();
		return empty($mvc[0]) ? 'index' : $mvc[0];
	}

	static function getController() {
		$mvc = self::getMVC();
		return empty($mvc[1]) ? 'index' : $mvc[1];
	}

	static function getAction() {
		$mvc = self::getMVC();
		return empty($mvc[2]) ? 'index' : $mvc[2];
	}

	static function getParams() {
		$mvc = self::getMVC();
		$result = empty($mvc['params']) ? array() : $mvc['params'];
		return $result;
	}

	static function actionToViewName($action) {
		$actionMethod = str_replace(' ', '', ucwords(str_replace('-', ' ', $action)));
		$actionMethod[0] = strtolower($actionMethod[0]);

		return $actionMethod;
	}

	static function callMVC($applicationFolderName = 'app', $useLayout = true) {

		try {

			$module = self::getModule();
			$controller = self::getController();
			$action = self::getAction();
			$params = self::getParams();
			
			// Parse the http header "Accept" value.
			$requestHeaders = getallheaders();
			if (empty($_REQUEST))
				$_REQUEST = !empty($params) ? $params : array();

			if (!empty($requestHeaders['Accept']) && empty($_REQUEST['format'])) {
				$accept = array_shift( explode(',', $requestHeaders['Accept']) );
				
				$format = 'json';
				$requestData = file_get_contents('php://input');
				
				$formattedData = null;

				// Convert the php input stream according to the "Accept" header. Set format.
				if (preg_match('/\;/', $accept)) {
					$accept = substr($accept, 0, strpos($accept, ';'));
				}
				
				switch ($accept) {

					case 'text/php':
						$format = 'php';
						if ($requestData) {
							// Unserialize the PHP data.
							$formattedData = unserialize($requestData);
						}
						break;
					case 'application/json':
						$format = 'json';
						if (is_string($requestData)) {
							$formattedData = json_decode($requestData, true);
						}
						break;
					default:
						$format = 'html';
						break;
				}
				
				if (!empty($requestData) && empty($formattedData)) {
					// Maybe it was NVP?
					
					parse_str($requestData, $formattedData);
				}
				
				// Merge php input vars with $_REQUEST
				if (is_array($formattedData)) {
					if (empty($_REQUEST)) {
						$_REQUEST = $formattedData;
					} else {
						$_REQUEST = array_merge($_REQUEST, $formattedData);
					}
				}

				// Set the format
				$_REQUEST['format'] = $format;

				$mvcParts = self::getMvc();
				foreach ($mvcParts as $i => $part) {
					if (is_numeric($part) && empty($_REQUEST['id'])) {
						$_REQUEST['id'] = $part;
						break;
					}
				}

			}

			$actionMethod = self::actionToViewName($action);

			$controllerClass = str_replace(' ', '', ucwords(str_replace('-', ' ', $controller)) . 'Controller');

			$controller_file = APP_PATH . '/' . $module . '/controller/' . $controllerClass . '.php';

			$file = APP_PATH . '/' . $module . '/view/' . $controller . '/' . $action . '.phtml';

			require_once($controller_file);
			
			$c = new $controllerClass;

			if ($c instanceof \App\Controller\Api || $c instanceof \App\Controller\Restful) {
				$actionMethod = self::actionToViewName($c->detectAction());
			}
			
			$a = $actionMethod . 'Action';
			
			if (method_exists($c, $a) && $c instanceof \App\Controller) {

				$c->preDispatch();
				$c->$a();

				$c->viewRender($file, $module, $controller, $action);
				
			} else {
				header('HTTP/1.0 404 Not Found');
				echo "404 - Page not found.";
				return false;
			}

			return true;
		} catch (Exception $e) {
			error_log((string) $e);
			header('HTTP/1.0 500 Internal Server Error');

			if (defined('APP_ENV') && APP_ENV == 'development') {
				echo $e->getMessage();
			}
			return false;
		}
	}

}