<?php
namespace Rotheart;

require_once('Request.php');

use Response;
use ReflectionClass;
use ReflectionMethod;
use Requests\Request;
use ValueError;

class Router {
	public string $use = "";
	public string $path_render = "/static";
	private $ntf = true;
	private array $metodos_permitidos = ["get", "post", "delete", "put", "patch"];
	private array $routes = [];

	protected function zip($arr1, $arr2) {
		$length = count($arr1) > count($arr2) ? count($arr2) : count($arr1);
		$zip = array();
		for($i = 0; $i < $length; $i++){
			$zip[$arr1[$i]] = $arr2[$i];
		}
		return $zip;
	}

	protected function preg_route($route, $patron){
		$partes_route = explode("/", $route);
		$partes_patron = explode("/", $patron);
		
		if(count($partes_patron) != count($partes_route)) return $patron;
		
		$match = true;
		$zipped = $this->zip($partes_patron, $partes_route);
		foreach ($zipped as $key => $value) {
			if($key !== $value && $key[0] !== ":"){
				$match = false;
				break;
			}else if($key === $value) unset($zipped[$key]);
		}
		return [$match ? $patron : $route, $zipped];
	}

	protected function process_args($route){
		$process = function ($arg) {
			$new_arg = explode("=", $arg);
			return [$new_arg[0] => $new_arg[1]];
		};

		$pre_args = substr($route, strpos($route, "?")+1);
		return array_map($process, explode("&", $pre_args))[0];
	}
	
	protected function getRoute($request_uri, $patron){
		$patron_route = strstr($request_uri, '?') ? strstr($request_uri, '?', true): $request_uri;
		
		$args = strstr($request_uri, '?') ? $this->process_args($request_uri) : [];

		$patron = $this->use.$patron;
		$pre_route = str_replace($this->use, '', $patron_route);
		
		$zip_vars = [];
		if(strstr($patron, ":")) {
			$pre_route = $this->preg_route($pre_route, $patron);
			$zip_vars = $pre_route[1];
			$pre_route = $pre_route[0];
		}
		
		return [$pre_route, $args, $zip_vars];
	}

	protected function add(string $tipo, string $patron, $function) {
		$tipo = strtolower($tipo);
		if(!in_array($tipo, $this->metodos_permitidos)) throw new ValueError("metodo no permitido");
		else $this->routes[$tipo][] = [$patron, $function];
	}

	protected function armarParametros($data, $functions) : array {
		$requests = new Request($functions);
		$requests->request["args"] = $data[1];
		
		$parameters = [
			"requests" => $requests,
			"res" => new Response(),
		];
		
		if($data[2]){
			$data_ = array();
			foreach ($data[2] as $key => $value) {
				$data_[substr($key, 1)] = $value;
			}
			$parameters = array_merge($parameters, $data_);
		}
		return $parameters;
	}

	protected function checkMethod($method){
		if($_SERVER['REQUEST_METHOD'] != strtoupper($method)){
			http_response_code(405);
			exit();
		}
	}

	protected function callCalback($data, $functions){
		$this->ntf = false;
		$parameters = $this->armarParametros($data, $functions);
			if(gettype($functions[0]) == "string" ){
				return $this->getReflection($functions[0], $parameters);
			}
		call_user_func_array($functions[0], $parameters);
	}

	protected function getReflection($name, $parameters) {
		$split = explode("::", $name);
		$class_name = $split[0]; $method_name = $split[1];

		$method = new ReflectionMethod($class_name, $method_name);
		$clase = new ReflectionClass($class_name);
		
		$instance = $clase->newInstance();
		return $method->invokeArgs($instance, $parameters);
	}

	public function get(string $patron, ...$functions) { $this->add("get", $patron, $functions); }
	public function post(string $patron, ...$functions){ $this->add("post", $patron, $functions); }
	public function delete(string $patron, ...$functions){ $this->add("delete", $patron, $functions); }

	public function run(){
		$route = $_SERVER['REQUEST_URI'];
		$method = $_SERVER["REQUEST_METHOD"];
		
		$routes = $this->routes[strtolower($method)];
		foreach ($routes as $key => $functions) {
			$patron = $functions[0];
			$data = $this->getRoute($route, $patron);
			$pre_route = $data[0];
			
			if($patron === $pre_route){
				// $this->checkMethod($method);
				$this->callCalback($data, $functions[1]);
			}
		}
		if($this->ntf) header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
	}
}
?>
