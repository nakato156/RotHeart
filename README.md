# Rotheart

Literalmente Roto heart (corazón roto) Es una herramienta que permite crear rutas amigables de forma muy rápida y sencilla.

## Uso
El eso es bastante sencillo, solo necesitamos crear una instancia de la clase `Router` y acceder a sus métodos `get`, `post`, etc. según lo que necesitemos.

Como primer parámetro va la ruta, los demás parametros son funciones que serán usadas como "middlewares", se ejecuta la primera función y si todo sale bien se procede a la siguiente función en caso exista.

```php
<?php
require_once("./vendor/autoload.php");

use Rotheart\Router;

$route = new Router();

$route->get('/', function(Request $requests, Response $res){
    return $res->render("index.twig"); # como motor de plantilla se usa twig
});

# siempre se debe colocar esta linea
$route->run();
?>
```
## Request
El objeto `Request` provee métodos para acceder al contenido de la petición

Dentro de la petición existen 3 formas en las que se puede obtener la información dependiendo de como se envie.
- `args` : Aquí se encuentra todos los datos de las peticiones que contienen argumentos en la URL.
- `data` : Aquí se encuentran los datos que vienen como cuerpo (body) de la petición.
- `files`: Aquí se encuentrasn los archivos que vienen en la petición.

Para acceder a esta infromación se puede usar el método `get`. `$requests->get("data");`  
Con esto ya podemos obtener la información que viene en el cuerpo de la petición, para acceder a los datos ya podemos hacerlo de la forma normal
```php
<?php
#...

$route->get('/', function(Request $requests, Response $res){
    $data = $requests->get("data");
    $username = $data["username"];
    return $res->json(["username" => $username]);
});
?>
```
## Response
El objeto `Response` Provee varios métodos para enviar respuestas al cliente.

- render : renderiza una página html usando twig como motor de plantilla `$res->render("pagina.twig");`
- json: Sirve para retornar una respuesta json, como parametro recibe un array `$res->json(["msg" => "hola Rotheart"]);`
- text: Retorna una respuesta en forma de texto plano `$res->text("Hola C13 :)");`
- abort: Aborta la petición. Como primer parámetro recibe el código de respuesta (Códigos HTTP). También acepta un segundo parámetro (opcional) que es el contenido adicional de la respuesta que peude ser un `string` o `array` que será transformado a JSON.
- redirect: Sirve para redireccionar la petición. `$res->redirect("/otra-ruta");`