<?php
namespace App\Models;

class Reservas extends DBAbstractModel{
    private static $instancia;
    private $id;
    private $id_instalacion;
    private $nombre;
    private $telefono;
    private $correo;
    private $fecha_inicio;
    private $fecha_final;
    private $estado;

    // Setters
    public function setId($id){
        $this->id = $id;
    }
    public function setIdinstalacion($id_instalacion){
        $this->id_instalacion = $id_instalacion;
    }
    public function setCorreo($correo){
        $this->correo = $correo;
    }

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
        $this->query = "INSERT INTO reservas (id_instalacion, nombre, telefono, correo, fecha_inicio, fecha_final, estado) VALUES (:id_instalacion, :nombre, :telefono, :correo, :fecha_inicio, :fecha_final, :estado)";
        $this->parametros['id_instalacion'] = $data['id_instalacion'];
        $this->parametros['nombre'] = $data['nombre'];
        $this->parametros['telefono'] = $data['telefono'];
        $this->parametros['correo'] = $this->correo;
        $this->parametros['fecha_inicio'] = $data['fecha_inicio'];
        $this->parametros['fecha_final'] = $data['fecha_final'];
        $this->parametros['estado'] = $data['estado'];
        $this->get_results_from_query();
    }

    public function get(){
        $this->query = "SELECT * FROM reservas WHERE id = :id";
        $this->parametros['id'] = $this->id;
        $this->get_results_from_query();
        return $this->rows;
    }

    public function edit($id = '', $data = array()){

    }

    public function delete($id= ''){
        $this->query = "DELETE FROM reservas WHERE id = :id";
        $this->parametros['id'] = $id;
        $this->get_results_from_query();
    }

    // Método que devuelve todas las rservas de un usuario
    public function getAllByUserEmail($email){
        $this->query = "SELECT * FROM reservas WHERE correo = :correo";
        $this->parametros['correo'] = $email;
        $this->get_results_from_query();
        return $this->rows;
    }
}