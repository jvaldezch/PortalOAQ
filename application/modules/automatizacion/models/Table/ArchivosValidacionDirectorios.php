<?php

class Automatizacion_Model_Table_ArchivosValidacionDirectorios {

    protected $id;
    protected $patente;
    protected $aduana;
    protected $yearPrefix;
    protected $directorio;
    protected $salida;

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

    function getDirectorio() {
        return $this->directorio;
    }

    function getSalida() {
        return $this->salida;
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

    function setDirectorio($directorio) {
        $this->directorio = $directorio;
    }

    function setSalida($salida) {
        $this->salida = $salida;
    }

    function getYearPrefix() {
        return $this->yearPrefix;
    }

    function setYearPrefix($yearPrefix) {
        $this->yearPrefix = $yearPrefix;
    }

}
