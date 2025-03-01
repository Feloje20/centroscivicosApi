<?php

namespace App\Controllers;

use \App\Models\Usuarios;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use \Exception;

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
        
        $email = $input['email'];
        $password = $input['password'];
        $dataUser = $this->users->login($email, $password);
        if ($dataUser) {
            $key = KEY;
            // Emisor del token
            $issuer_claim = "http://apirestcontactos.local/";
            // Audiencia del token
            $audience_claim = "http://apirestcontactos.local/";
            $issuedat_claim = time();
            $notbefore_claim = time();
            $expire_claim = $issuedat_claim + 3600;

            // Información del token
            $token = array(
                "iss" => $issuer_claim,
                "aud" => $audience_claim,
                "iat" => $issuedat_claim,
                "nbf" => $notbefore_claim,
                "exp" => $expire_claim,
                "data" => array(
                    "email" => $email,
                    "userId" => $dataUser['id'],
                    "usuario" => $dataUser['usuario']
                )
            );

            // Generamos el token JWT
            $jwt = JWT::encode($token, $key, 'HS256'); //Genera el token JWT
            $res = json_encode (
                array(
                    "message" => "Succesful login.",
                    "jwt" => $jwt,
                    "email" => $email,
                    "expireAt" => $expire_claim
                )
            );
            $response['status_code_header'] = "HTTP/1.1 201 Created";
            $response['body'] = $res; // Cuerpo con la respuesta con el token
        } else {
            $response['status_code_header'] = "HTTP/1.1 401 Login Failed";
            $response['body'] = null;
        }

        return $response;  
    }

    public function refreshToken()
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if (!$authHeader) {
            http_response_code(401);
            $response['status_code_header'] = "HTTP/1.1 401 Unauthorized";
            $response['body'] = json_encode(["message" => "No token provided"]);
            return $response;
        }

        // Extraer el token
        $jwt = explode(" ", $authHeader)[1] ?? null;
        if (!$jwt) {
            http_response_code(401);
            $response['status_code_header'] = "HTTP/1.1 401 Unauthorized";
            $response['body'] = json_encode(["message" => "Token format invalid"]);
            return $response;
        }

        try {
            $decoded = JWT::decode($jwt, new Key(KEY, 'HS256'));
            $userId = $decoded->data->userId;
            $usuario = $decoded->data->usuario;
            $userEmail = $decoded->data->email;

            // Generar un nuevo token con nueva expiración
            $issuedAt = time();
            $expireAt = $issuedAt + 3600; // Nuevo token válido por 1 hora

            $newToken = JWT::encode([
                "iss" => "http://apirestcontactos.local/",
                "iat" => $issuedAt,
                "exp" => $expireAt,
                "data" => [
                    "userId" => $userId,
                    "usuario" => $usuario,
                    "email" => $userEmail
                ]
            ], KEY, 'HS256');

            $response['status_code_header'] = "HTTP/1.1 201 Created";
            $response['body'] = json_encode([
                "message" => "Token refreshed",
                "jwt" => $newToken,
                "expireAt" => $expireAt
            ]);
        } catch (Exception $e) {
            http_response_code(401);
            $response['status_code_header'] = "HTTP/1.1 401 Unauthorized";
            $response['body'] = json_encode(["message" => "Invalid token", "error" => $e->getMessage()]);
        }

        return $response;
    }
}