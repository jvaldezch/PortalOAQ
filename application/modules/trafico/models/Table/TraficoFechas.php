<?php

class Trafico_Model_Table_TraficoFechas {

    protected $id;
    protected $idTrafico;
    protected $fecha;
    protected $tipo;
    protected $creado;
    protected $creadoPor;
    protected $actualizado;
    protected $actualizadoPor;

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

    function getIdTrafico() {
        return $this->idTrafico;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getTipo() {
        return $this->tipo;
    }

    function getCreado() {
        return $this->creado;
    }

    function getCreadoPor() {
        return $this->creadoPor;
    }

    function getActualizado() {
        return $this->actualizado;
    }

    function getActualizadoPor() {
        return $this->actualizadoPor;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setIdTrafico($idTrafico) {
        $this->idTrafico = $idTrafico;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    function setCreado($creado) {
        $this->creado = $creado;
    }

    function setCreadoPor($creadoPor) {
        $this->creadoPor = $creadoPor;
    }

    function setActualizado($actualizado) {
        $this->actualizado = $actualizado;
    }

    function setActualizadoPor($actualizadoPor) {
        $this->actualizadoPor = $actualizadoPor;
    }

}
