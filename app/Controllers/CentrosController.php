<?php

namespace App\Controllers;

use App\Models\Centros;

class CentrosController {
    private $requestMethod;

    private $centros;
    private $uri;

    public function __construct($requestMethod)
    {
        $this->requestMethod = $requestMethod;
        $this->centros = Centros::getInstancia();
        $this->uri = str_replace(BASE_URL, '', $_SERVER['REQUEST_URI']);
    }

    /**
     * Funcion que procesa la peticion
     * return: Respuesta de la petición
     */
    public function processRequest(){
        // Si el método no es correcto, devuelve un error.
        if ($this->requestMethod !== 'GET') {
            $response = $this->methodNotAllowedResponse();
        } else {
            // En función de la URI, se ejecuta una función u otra
            switch ($this->uri) {
                case '/centros':
                    $response = $this->getAllCentros();
                    break;
                case (preg_match('/\/centros\/\d+/', $this->uri) ? true : false):
                    $response = $this->getCentro();
                    break;
                default:
                    $response = $this->notFoundResponse();
                    break;
            }
        }

        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    // Método que devuelve todos los centros
    private function getAllCentros(){
        $result = $this->centros->getAll();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result );
        return $response;
    }

    // Método que devuelve toda la info de un centro
    private function getCentro(){
        $id = explode('/', $this->uri)[2];
        $this->centros->setId($id);
        $result = $this->centros->get($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }
    
    // Método que devuelve un error 404
    public function notFoundResponse(){
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }

    // Método que devuelve un error 405
    private function methodNotAllowedResponse(){
        $response['status_code_header'] = 'HTTP/1.1 405 Method Not Allowed';
        $response['body'] = null;
        return $response;
    }

    
}