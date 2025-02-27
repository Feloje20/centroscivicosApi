<?php
namespace App\Models;

class Usuarios extends DBAbstractModel{
    private static $instancia;
    private $id;
    private $usuario;
    private $password;
    private $email;

    // Modelo singleton
    public static function getInstancia()
    {
        if (!isset(self::$instancia)) {
            $miClase = __CLASS__;
            self::$instancia = new $miClase;
        }
        return self::$instancia;
    }

    public function set($data = array()){
        foreach ($data as $campo => $valor) {
            $this->$campo = $valor;
        }

        $this->query = "INSERT INTO usuarios (usuario, password, email) 
                VALUES (:usuario, :password, :email)";
        $this->parametros['usuario'] = $this->usuario;
        $this->parametros['password'] = $this->password;
        $this->parametros['email'] = $this->email;
        $this->get_results_from_query();
        $this->mensaje = "Usuario agregado";
    }

    public function get($id = ''){
        if ($id != '') {
            $this->query = "SELECT * FROM usuarios WHERE id = :id";
            $this->parametros['id'] = $id;
            $this->get_results_from_query();
        }
        if (count($this->rows) == 1) {
            foreach ($this->rows[0] as $propiedad => $valor) {
                $this->$propiedad = $valor;
            }
            $this->mensaje = "Usuario encontrado";
        } else {
            $this->mensaje = "Usuario no encontrado";
        }
        return $this->rows[0]??null;
    }

    public function edit($id = '', $data = array()){
        
    }

    public function delete($id= ''){
        
    }

    // Método de login
    public function login($usuario, $password){
        $this->query = "SELECT * FROM usuarios WHERE usuario = :usuario AND password = :password";
        $this->parametros['usuario'] = $usuario;
        $this->parametros['password'] = $password;
        $this->get_results_from_query();
        if (count($this->rows) == 1) {
            foreach ($this->rows[0] as $propiedad => $valor) {
                $this->$propiedad = $valor;
            }
            $this->mensaje = "Usuario encontrado";
        } else {
            $this->mensaje = "Usuario no encontrado";
        }
        return $this->rows[0]??null;
    }
}