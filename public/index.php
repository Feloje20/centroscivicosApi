<?php
require "../bootstrap.php";
require_once "../vendor/autoload.php";

use App\Models\Contactos;
use App\Core\Router;
use App\Controllers\UsuariosController;
use App\Controllers\AuthController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Ponemos las cabeceras
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE"); 

// Obtener método de la solicitud
$request_method = $_SERVER['REQUEST_METHOD'];

if ($request_method == "OPTIONS") {
    http_response_code(200);
    exit();
}

$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Proceso de login
if ($request_uri == '/login') {
    $auth = new AuthController($request_method);
    if (!$auth->loginFromRequest()) {
        exit(http_response_code(401));
    }
}

// Obtener input JSON (si existe)
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// Obtener cabecera de autorización
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

// Validar si hay token antes de intentar procesarlo
$jwt = null;
if ($authHeader) {
    $arr = explode(" ", $authHeader);
    if (count($arr) == 2) {
        $jwt = $arr[1];
    }
}

// Validar JWT

if ($jwt) {
    try {
        $decoded = JWT::decode($jwt, new Key(KEY, 'HS256'));

        // Obtenemos la ID del usuarios en la decodificación del token
        $userId = $decoded->data->userId;
    } catch (Exception $e) {
        echo json_encode([
            "message" => "Access denied.",
            "error" => $e->getMessage()
        ]);
        exit(http_response_code(401));
    }
} else {
    echo json_encode(["message" => "No authorization token provided."]);
    exit(http_response_code(401));
} 

// Configuración de rutas
$router = new Router();
// ******** USUARIOS **********
$router->add(array(
    "name" => "home",
    "path" => "/^\/user$/",
    "action" => UsuariosController::class
));

// Buscar la ruta
$route = $router->match($request_uri);
if ($route) {
    $controllerName = $route['action'];
    $controller = new $controllerName($request_method, $userId);
    $controller->processRequest();
} else {
    http_response_code(404);
    echo json_encode(["message" => "Not Found"]);
}
