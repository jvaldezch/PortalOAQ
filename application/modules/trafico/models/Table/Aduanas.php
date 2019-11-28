<?php

class Trafico_Model_Table_Aduanas {

    protected $id;
    protected $patente;
    protected $aduana;
    protected $nombre;
    protected $tipoAduana;
    protected $corresponsal;
    protected $activo;

    public function __construct(array $options = null) {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function __set($name, $value) {
        $method = "set" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid invoice property");
        }
        $this->$method($value);
    }

    public function __get($name) {
        $method = "get" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid invoice property");
        }
        return $this->$method();
    }

    public function setOptions(array $options) {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = "set" . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    function getId() {
        return $this->id;
    }

    function getPatente() {
        return $this->patente;
    }

    function getAduana() {
        return $this->aduana;
    }

    function getNombre() {
        return $this->nombre;
    }

    function getTipoAduana() {
        return $this->tipoAduana;
    }

    function getCorresponsal() {
        return $this->corresponsal;
    }

    function getActivo() {
        return $this->activo;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setPatente($patente) {
        $this->patente = $patente;
    }

    function setAduana($aduana) {
        $this->aduana = $aduana;
    }

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    function setTipoAduana($tipoAduana) {
        $this->tipoAduana = $tipoAduana;
    }

    function setCorresponsal($corresponsal) {
        $this->corresponsal = $corresponsal;
    }

    function setActivo($activo) {
        $this->activo = $activo;
    }

}
