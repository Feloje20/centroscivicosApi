<?php
require "../bootstrap.php";
require_once "../vendor/autoload.php";

use App\Models\Contactos;
use App\Core\Router;
use App\Controllers\ContactosController;
use App\Controllers\AuthController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Ponemos las cabeceras
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE"); 

// $method = $_SERVER['REQUEST_METHOD'];
// if($method == "OPTIONS") {
//     die();
// }
$request_method = $_SERVER['REQUEST_METHOD'];
$request = $_SERVER['REQUEST_METHOD'];

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $request);

// Si existe recuperamos el id
$userId = null;
if (isset($uri[2])) {
    $userId = (int) $uri[2];
}

// Proceso de login
if ($request == '/login/') {
    $auth = new AuthController($request_method);
    if (!$auth->loginFromRequest()) {
        exit(http_response_code(401));
    }
}

$input = (array) json_decode(file_get_contents('php://input'), TRUE);
$authHeader = $_SERVER['HTTP_AUTHORIZATION'];
$arr = explode(" ", $authHeader);
$jwt = $arr[1];

if($jwt) {
    try {
        $decoded = JWT::decode($jwt, new Key(KEY, 'HS256') );
    } catch (Exception $e) {
        echo json_encode(array(
            "message" =>"Acces denied.",
            "error" => $e->getMessage()));
        exit(http_response_code(401));
    }
}

$router = new Router();
$router->add(array(
    "name" => "home",
    "path" => "/^\/contactos\/([0-9]+)?$/",
    "action" => ContactosController::class
));

$route = $router->match($request);
if ($route){
    $controllerName = $route['action'];
    $controller = new $controllerName($request_method, $userId);
    $controller->processRequest();
} else{
    $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
    $response['body'] = null;
    echo json_encode($response);
}
