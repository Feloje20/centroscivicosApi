<?php
namespace App\Models;

class Actividades extends DBAbstractModel{
    private static $instancia;
    private $id;
    private $id_centro;
    private $nombre;
    private $descripcion;
    private $fecha_inicio;
    private $fecha_final;
    private $horario;
    private $plaza;

    // Setters
    public function setId($id){
        $this->id = $id;
    }
    public function setIdCentro($id_centro){
        $this->id_centro = $id_centro;
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

    }

    public function get(){
        $this->query = "SELECT * FROM actividades WHERE id = :id";
        $this->parametros['id'] = $this->id;
        $this->get_results_from_query();
        return $this->rows;
    }

    public function edit($id = '', $data = array()){

    }

    public function delete($id= ''){

    }

    // Método que devuelve todas las actividades de un centro
    public function getAllByCentroId(){
        $this->query = "SELECT * FROM actividades WHERE id_centro = :id_centro";
        $this->parametros['id_centro'] = $this->id_centro;
        $this->get_results_from_query();
        return $this->rows;
    }

    // Método que devuelve todas las actividades (con filtro)
    public function getAll($searchQuery = ''){
        $this->query = "SELECT * FROM actividades";
        if (!empty($searchQuery)) {
            $this->query .= " WHERE nombre LIKE :searchQuery OR descripcion LIKE :searchQuery";
            $this->parametros['searchQuery'] = '%' . $searchQuery . '%';
        }
        $this->get_results_from_query();
        return $this->rows;
    }
}