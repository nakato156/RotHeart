<?php
class Token {
    private $token;
    private $expira;

    public function __construct(){
        $this->token = bin2hex(random_bytes(32));
        $this->expira = strtotime(date("Y-m-d H:i:00", strtotime(date("Y-m-d H:i:00", time())."+ 1 hour")));
    }

    public function check(string $token) : bool {
        $actual = strtotime(date("d-m-Y H:i:00", time()));
        if( $this->expira > $actual && $token == $this->token) return true;
        return false;
    }

    public function __toString() : string{
        return $this->token;
    }
};

class CSRF {
    public static function csrf_token() : string {
        $_SESSION["token"] = bin2hex(random_bytes(32));
        return $_SESSION["token"];
    }
    
    private static function sendError(){
        echo json_encode(["Error"=> "Sesion caducada","msg" => "Sesion caducada. Recarge la pagina"]);
        return http_response_code(401);
    }

    public static function verificar(string $token) : void{
        if(!isset($_SESSION["token"]) || $token != $_SESSION["token"]) {
            CSRF::sendError();
            exit();
        }
    }
};

?>