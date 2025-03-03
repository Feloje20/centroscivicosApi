<?php
namespace App\Models;

class Instalaciones extends DBAbstractModel{
    private static $instancia;
    private $id;
    private $id_centro;
    private $nombre;
    private $descripcion;
    private $capacidad_maxima;

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
        $this->query = "SELECT * FROM instalaciones WHERE id = :id";
        $this->parametros['id'] = $this->id;
        $this->get_results_from_query();
        return $this->rows;
    }

    public function edit($id = '', $data = array()){

    }

    public function delete($id= ''){

    }

    // Método que devuelve todas las instalaciones de un centro
    public function getAllByCentroId(){
        $this->query = "SELECT * FROM instalaciones WHERE id_centro = :id_centro";
        $this->parametros['id_centro'] = $this->id_centro;
        $this->get_results_from_query();
        return $this->rows;
    }

    // Método que devuelve todas las instalaciones (NECESITA FILTRO***************)
    public function getAll($searchQuery = ''){
        $this->query = "SELECT * FROM instalaciones";
        if (!empty($searchQuery)) {
            $this->query = "SELECT * FROM instalaciones WHERE nombre LIKE :searchQuery OR descripcion LIKE :searchQuery";
            $this->parametros['searchQuery'] = '%' . $searchQuery . '%';
        }
        $this->get_results_from_query();
        return $this->rows;
    }
}