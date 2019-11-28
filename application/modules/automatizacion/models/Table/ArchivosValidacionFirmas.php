<?php

class Automatizacion_Model_Table_ArchivosValidacionFirmas {

    protected $id;
    protected $idArchivoValidacion;
    protected $patente;
    protected $pedimento;
    protected $firma;
    protected $creado;

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

    function getIdArchivoValidacion() {
        return $this->idArchivoValidacion;
    }

    function getPatente() {
        return $this->patente;
    }

    function getPedimento() {
        return $this->pedimento;
    }

    function getFirma() {
        return $this->firma;
    }

    function getCreado() {
        return $this->creado;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setIdArchivoValidacion($idArchivoValidacion) {
        $this->idArchivoValidacion = $idArchivoValidacion;
    }

    function setPatente($patente) {
        $this->patente = $patente;
    }

    function setPedimento($pedimento) {
        $this->pedimento = $pedimento;
    }

    function setFirma($firma) {
        $this->firma = $firma;
    }

    function setCreado($creado) {
        $this->creado = $creado;
    }

}
