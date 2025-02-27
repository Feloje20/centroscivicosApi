<?php

namespace App\Controllers;

class CentrosController {
    private $requestMethod;
    private $usuariosId;

    private $usuarios;
    private $uri;

    public function __construct($requestMethod)
    {
        $this->requestMethod = $requestMethod;
        $this->usuariosId = $usuariosId;
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
                $response = $this->getUsuarios($this->usuariosId);
                break;
            case 'POST':
                // Nuestro POST de usuarios controlar varias peticiones en función de la ruta
                if ($this->uri === '/login') {
                    $response = $this->loginFromRequest();
                } elseif ($this->uri === '/register') {
                    $response = $this->createUsuarios();
                }
                break;
            case 'PUT':
                $response = $this->updateUsuarios($this->usuariosId);
                break;
            case 'DELETE':
                $response = $this->deleteUsuarios($this->usuariosId);
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
        $auth->loginFromRequest();
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

    // Método que devuelve un error 404
    public function notFoundResponse(){
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }

    
}