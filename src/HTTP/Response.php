<?php
namespace Rotheart;
require_once( __DIR__.'./../modules/CSRF.php');

class Response {
    private $loader, $twig;

    public function __construct() {
        $this->loader = new \Twig\Loader\FilesystemLoader('./static');
        $this->twig = new \Twig\Environment($this->loader, []);

        $function = new \Twig\TwigFunction('csrf_token', function () {return CSRF::csrf_token();});
        $function2 = new \Twig\TwigFunction('getUserPath', function (string $path, string $username){
            $i = strpos($username, $path);
            return substr($path, $i + strlen($username));
        });
        $this->twig->addFunction($function);
        $this->twig->addFunction($function2);
    }

    public function render(string $name, array $vars = []) { echo $this->twig->render($name, $vars); }

    public function json(array $res) { echo json_encode($res); }
    public function text(string $res){ echo $res; }
    public function abort(int $status_code = 0, $data = null) { 
        if($status_code) http_response_code($status_code);
        if ($data && gettype($data) == "string") echo $data;
        else if($data) echo json_encode($data);
        exit();
    }
    public function redirect(string $route){ return header("Location: $route"); }
};

?>