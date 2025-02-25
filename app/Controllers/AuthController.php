<?php

namespace App\Controllers;

use \App\Models\Usuarios;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class AuthController {
    private $requestMethod;
    private $userId;
    private $users;

    public function __construct($requestMethod)
    {
        $this->requestMethod = $requestMethod;
        $this->users = Usuarios::getInstancia();
    }

    public function loginFromRequest()
    {
        // Leemos el flujo de entrada
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        // Determinamos si el formato de entrada es correcto.
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(["message" => "El Json recibido no es válido", "error" => json_last_error_msg()]);
            exit();
        }   
        
        $usuario = $input['usuario'];
        $password = $input['password'];
        $dataUser = $this->users->login($usuario, $password);

        if ($dataUser) {
            $key = KEY;
            // Emisor del token
            $issuer_claim = "http://apirestcontactos.local/";
            // Audiencia del token
            $audience_claim = "http://apirestcontactos.local/";
            $issuedat_claim = time();
            $notbefore_claim = time();
            $expire_claim = $issuedat_claim + 3600;

            $token = array(
                "iss" => $issuer_claim,
                "aud" => $audience_claim,
                "iat" => $issuedat_claim,
                "nbf" => $notbefore_claim,
                "exp" => $expire_claim,
                "data" => array(
                    "usuario" => $usuario
                )
            );

            $jwt = JWT::encode($token, $key, 'HS256'); //Genera el token JWT
            $res = json_encode (
                array(
                    "message " => "Succesful login.",
                    "jwt" => $jwt,
                    "usuario" => $usuario,
                    "expireAt" => $expire_claim
                )
            );

            $response['status_code_header'] = "HTTP/1.1 201 Created";
            $response['body'] = $res; // Cuerpo con la respuesta con el token
        } else {
            $response['status_code_header'] = "HTTP/1.1 401 Login Failed";
            $response['body'] = null;
        }

        header($response['status_code_header']);  // Envía el encabezado de la respuesta
        if ($response['body']) {
            echo $response['body']; // Envía el cuerpo de la respuesta
        }        
    }
}