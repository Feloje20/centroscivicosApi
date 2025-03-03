<?php
namespace App\Models;
use App\Models\Instalaciones;
use App\Models\Centros;

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
    
        // Instancias de las clases de Instalación y Centro
        $instalacion = Instalaciones::getInstancia();
        $centro = Centros::getInstancia();
    
        foreach ($this->rows as &$reserva) {
            // Obtener los datos de la instalación
            $instalacion->setId($reserva['id_instalacion']);
            $instalacionDatos = $instalacion->get();  // Esto debería devolver un único valor o null
    
            if ($instalacionDatos) {
                // Asignar la instalación a la reserva
                $reserva['instalacion'] = $instalacionDatos;
                // Verificar si existe un 'id_centro' y obtener los datos del centro
                if (isset($reserva['instalacion'][0]['id_centro'])) {
                    $centro->setId($reserva['instalacion'][0]['id_centro']);
                    
                    $centroDatos = $centro->getCentro();  // Suponiendo que 'getCentro' devuelve un solo objeto de centro
    
                    if ($centroDatos) {
                        // Asignar los datos del centro a la instalación
                        $reserva['instalacion'][0]['centro'] = $centroDatos;
                    } else {
                        // Si no se encuentra el centro, asignar un valor por defecto o manejarlo
                        $reserva['instalacion'][0]['centro'] = null;
                    }
                } else {
                    // Si no hay 'id_centro', asignamos null
                    $reserva['instalacion']['centro'] = null;
                }
            } else {
                // Si no se encuentra la instalación, asignar null
                $reserva['instalacion'] = null;
            }
        }
    
        return $this->rows;
    }
}