<?php
namespace Rotheart;

require_once('Response.php');

use ArrayIterator;
use ReflectionClass;
use ReflectionMethod;
use Response;

class Request {
    public array $request = array();
    private $funcs;
    private int $pos_func = 0;

    public function __construct(array $functions) {
        foreach (apache_request_headers() as $header => $value) {
            $this->request[$header] = $value;
        }
        $this->request = array_merge($this->request, [
            "args" => [],
            "data" => empty($_POST) ? json_decode(file_get_contents("php://input"), true) : $_POST,
            "files" => $_FILES,
        ]);
        $this->funcs = new ArrayIterator($functions);
    }

    public function next(array $args = null){
        if($this->pos_func + 1 >= $this->funcs->count()) return;
        
        $parameters = [$this, new Response()];
        if($args) $parameters = array_merge($parameters, $args);

        $function = $this->funcs[++$this->pos_func];
        
        if(gettype($function) == "string" ){
            $split = explode("::", $function);
            $class_name = $split[0]; $method_name = $split[1];

            $method = new ReflectionMethod($class_name, $method_name);
            $clase = new ReflectionClass($class_name);
            $instance = $clase->newInstance();
            return $method->invokeArgs($instance, $parameters);
            
        }else return $function(...$parameters);
    }

    public function get($key, $default = null){
        if(array_key_exists($key, $this->request)) return $this->request[$key];
        return $default;
    }
};

?>