<?php

namespace App\Controllers;

use App\Models\Usuarios;
use App\Models\Reservas;
use App\Models\Inscripciones;
use App\Controllers\AuthController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UsuariosController {
    private $requestMethod;
    private $usuariosId;
    private $userEmail;

    private $usuarios;
    private $uri;

    public function __construct($requestMethod, $jwt = null)
    {
        $this->requestMethod = $requestMethod;
        if ($jwt !== null) {
            $decodedToken = $this->decodeToken($jwt);
            $this->usuariosId = $decodedToken['userId'];
            $this->userEmail = $decodedToken['email'];
        }
        $this->usuarios = Usuarios::getInstancia();
        $this->uri = str_replace(BASE_URL, '', $_SERVER['REQUEST_URI']);
    }

    /**
     * Funcion que procesa la peticion
     * return: Respuesta de la petición
     */
    public function processRequest(){
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->uri === '/reservas') {
                    $response = $this->getUserReservas();
                } elseif ($this->uri === '/inscripciones') {
                    $response = $this->getUserInscripciones();
                } else {
                    $response = $this->getUsuarios($this->usuariosId);
                }
                break;
            case 'POST':
                // Nuestro POST de usuarios controlar varias peticiones en función de la ruta
                if ($this->uri === '/login') {
                    $response = $this->loginFromRequest();
                } elseif ($this->uri === '/register') {
                    $response = $this->createUsuarios();
                } elseif ($this->uri === '/token/refresh') {
                    $response = $this->refreshToken();
                } elseif ($this->uri === '/reservas') {
                    $response = $this->createReservas();
                } elseif ($this->uri === '/inscripciones') {
                    $response = $this->createInscripciones();
                }
                break;
            case 'PUT':
                $response = $this->updateUsuarios($this->usuariosId);
                break;
            case 'DELETE':
                // Si las rutas tienen un ID después de las palabras, se elimina el recurso
                if (preg_match('/\/reservas\/\d+/', $this->uri) ? true : false) {
                    $response = $this->deleteReservas();
                } elseif (preg_match('/\/inscripciones\/\d+/', $this->uri) ? true : false) {
                    $response = $this->deleteInscripciones();
                } else {
                    $response = $this->deleteUsuarios($this->usuariosId);
                }
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }

        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    // Método de login
    private function loginFromRequest(){
        $auth = new AuthController($this->requestMethod);
        $response = $auth->loginFromRequest();
        return $response;
    }

    // Método de refresco de token
    private function refreshToken(){
        $auth = new AuthController($this->requestMethod);
        $auth->refreshToken();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($auth);
        return $response;
    }

    // Método que obtiene la información de un usuario
    private function getUsuarios($id){
        $result = $this->usuarios->get($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    // Método de registro de usuarios
    public function createUsuarios(){
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (!$this->validateUsuarios($input)) {
            return $this->notFoundResponse();
        }
        $this->usuarios->set($input);
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = null;
        return $response;
    }

    // Método que actualiza la información del usuario
    private function updateUsuarios($id){
        $result = $this->usuarios->get($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (!$this->validateUsuarios($input)) {
            return $this->notFoundResponse();
        }
        $this->usuarios->edit($id, $input);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    // Método que valida la información recibida del cliente
    private function validateUsuarios($input){
        if (!isset($input['usuario']) || !isset($input['password']) || !isset($input['email'])) {
            return false;
        }
        return true;
    }

    // Método que elimina un usuario
    public function deleteUsuarios($id) {
        $result = $this->usuarios->get($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $this->usuarios->delete($id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    // ********** RESERVAS **********
    // Método para crear una reserva
    public function createReservas(){
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (!$this->validateReservas($input)) {
            return $this->notFoundResponse();
        }
        $reservas = Reservas::getInstancia();
        $reservas->setCorreo($this->userEmail);
        $reservas->set($input);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    // Método para validar reservas
    private function validateReservas($input){
        if (!isset($input['id_instalacion']) || !isset($input['nombre']) || !isset($input['telefono']) || !isset($input['fecha_inicio']) || !isset($input['fecha_final']) || !isset($input['estado'])) {
            return false;
        }
        return true;
    }

    // Método para eliminar una reserva por ID
    public function deleteReservas(){
        $id = explode('/', $this->uri)[2];
        $reservas = Reservas::getInstancia();
        $reservas->setId($id);
        $result = $reservas->get();
        if (!$result) {
            return $this->notFoundResponse();
        }
        // Comprobamos si la reserve pertenece al usuario
        if ($result[0]['correo'] !== $this->userEmail) {
            return $this->notFoundResponse();
        }
        $reservas->delete($id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    // Método para recuperar todas las reservas de un usuario usando el email
    public function getUserReservas(){
        $reservas = Reservas::getInstancia();
        $result = $reservas->getAllByUserEmail($this->userEmail);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    // ********** INSCRIPCIONES **********
    // Método para crear una inscripción
    public function createInscripciones(){
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (!$this->validateInscripciones($input)) {
            return $this->notFoundResponse();
        }
        $inscripciones = Inscripciones::getInstancia();
        $inscripciones->setCorreo($this->userEmail);
        $inscripciones->set($input);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    // Método para validar inscripciones
    private function validateInscripciones($input){
        if (!isset($input['id_actividad']) || !isset($input['nombre']) || !isset($input['telefono']) || !isset($input['fecha_inscripcion']) || !isset($input['estado'])) {
            return false;
        }
        return true;
    }

    // Método para eliminar una inscripción por ID
    public function deleteInscripciones(){
        $id = explode('/', $this->uri)[2];
        $inscripciones = Inscripciones::getInstancia();
        $inscripciones->setId($id);
        $result = $inscripciones->get();
        if (!$result) {
            return $this->notFoundResponse();
        }
        // Comprobamos si la inscripción pertenece al usuario
        if ($result[0]['correo'] !== $this->userEmail) {
            return $this->notFoundResponse();
        }
        $inscripciones->delete($id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    // Método para recuperar todas las inscripciones de un usuario usando el email
    public function getUserInscripciones(){
        $inscripciones = Inscripciones::getInstancia();
        $result = $inscripciones->getAllByUserEmail($this->userEmail);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    // ************ MISCELANIOS ****************
    // Método que devuelve un error 404
    public function notFoundResponse(){
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }

    // Método que decodifica el token y obtiene la id del usuario y el email
    public function decodeToken($jwt){
        $decoded = JWT::decode($jwt, new Key(KEY, 'HS256'));
        $userId = $decoded->data->userId;
        $email = $decoded->data->email;
        return array('userId' => $userId, 'email' => $email);
    }
}