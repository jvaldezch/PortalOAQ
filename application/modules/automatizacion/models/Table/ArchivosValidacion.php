<?php

class Automatizacion_Model_Table_ArchivosValidacion {

    protected $id;
    protected $patente;
    protected $aduana;
    protected $archivo;
    protected $archivoNombre;
    protected $diaJuliano;
    protected $archivoNum;
    protected $tipo;
    protected $hash;
    protected $contenido;
    protected $usuario;
    protected $analizado;
    protected $error;
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

    function getPatente() {
        return $this->patente;
    }

    function getAduana() {
        return $this->aduana;
    }

    function getArchivo() {
        return $this->archivo;
    }

    function getArchivoNombre() {
        return $this->archivoNombre;
    }

    function getDiaJuliano() {
        return $this->diaJuliano;
    }

    function getArchivoNum() {
        return $this->archivoNum;
    }

    function getTipo() {
        return $this->tipo;
    }

    function getHash() {
        return $this->hash;
    }

    function getContenido() {
        return $this->contenido;
    }

    function getAnalizado() {
        return $this->analizado;
    }

    function getCreado() {
        return $this->creado;
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

    function setArchivo($archivo) {
        $this->archivo = $archivo;
    }

    function setArchivoNombre($archivoNombre) {
        $this->archivoNombre = $archivoNombre;
    }

    function setDiaJuliano($diaJuliano) {
        $this->diaJuliano = $diaJuliano;
    }

    function setArchivoNum($archivoNum) {
        $this->archivoNum = $archivoNum;
    }

    function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    function setHash($hash) {
        $this->hash = $hash;
    }

    function setContenido($contenido) {
        $this->contenido = $contenido;
    }

    function setAnalizado($analizado) {
        $this->analizado = $analizado;
    }

    function setCreado($creado) {
        $this->creado = $creado;
    }

    function getError() {
        return $this->error;
    }

    function setError($error) {
        $this->error = $error;
    }
    
    function getUsuario() {
        return $this->usuario;
    }

    function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

}
