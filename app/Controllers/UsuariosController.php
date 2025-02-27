<?php

namespace App\Controllers;

use App\Models\Usuarios;

class UsuariosController {
    private $requestMethod;
    private $usuariosId;

    private $usuarios;

    public function __construct($requestMethod, $usuariosId)
    {
        $this->requestMethod = $requestMethod;
        $this->usuariosId = $usuariosId;
        $this->usuarios = Usuarios::getInstancia();
    }

    /**
     * Funcion que procesa la peticion
     * return: Respuesta de la petición
     */
    public function processRequest(){
        echo (var_dump($this->usuariosId));
        switch ($this->requestMethod) {
            case 'GET':
                $response = $this->getUsuarios($this->usuariosId);
                break;
            case 'POST':
                $input = (array) json_decode(file_get_contents('php://input'), TRUE); 
                $response = $this->createUsuarios();
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

    private function getUsuarios($id){
        $result = $this->usuarios->get($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

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

    private function validateUsuarios($input){
        if (!isset($input['nombre']) || !isset($input['telefono']) || !isset($input['email'])) {
            return false;
        }
        return true;
    }

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

    public function notFoundResponse(){
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }

    
}