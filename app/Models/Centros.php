<?php
namespace App\Models;

use App\Models\Instalaciones;
class Centros extends DBAbstractModel{
    private static $instancia;
    private $id;
    private $nombre;
    private $direccion;
    private $telefono;
    private $horario;
    private $foto;
    private $instalaciones = [];
    private $actividades = [];

    // Setters
    public function setId($id){
        $this->id = $id;
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
        $this->query = "SELECT * FROM centros_civicos WHERE id = :id";
        $this->parametros['id'] = $this->id;
        $this->get_results_from_query();
        if (count($this->rows) == 1) {
            foreach ($this->rows[0] as $propiedad => $valor) {
                $this->$propiedad = $valor;
            }

            // Obtenemos las instalaciones del centro
            $instalaciones = Instalaciones::getInstancia();
            $instalaciones->setIdCentro($this->id);
            $this->instalaciones = $instalaciones->getAllByCentroId();
            $this->rows[0]['instalaciones'] = $this->instalaciones;

            // Obtenemos las actividades del centro
            $actividades = Actividades::getInstancia();
            $actividades->setIdCentro($this->id);
            $this->actividades = $actividades->getAllByCentroId();
            $this->rows[0]['actividades'] = $this->actividades;
            
            $this->mensaje = "Centro encontrado";
        } else {
            $this->mensaje = "Centro no encontrado";
        }
        return $this->rows[0]??null;
    }

    public function edit($id = '', $data = array()){

    }

    public function delete($id= ''){

    }

    // Método que devuelve todos los centros
    public function getAll(){
        $this->query = "SELECT * FROM centros_civicos";
        $this->get_results_from_query();
        return $this->rows;
    }

    // Datos de un centro especifico, solo el centro
    public function getCentro(){
        $this->query = "SELECT * FROM centros_civicos WHERE id = :id";
        $this->parametros['id'] = $this->id;
        $this->get_results_from_query();
        return $this->rows;
    }
}