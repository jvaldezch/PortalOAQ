<?php

class Administracion_Model_Table_CorresponsalesCuentas {

    protected $id;
    protected $ingresos;
    protected $costos;
    protected $nombre;

    public function __construct(array $options = null) {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function __set($name, $value) {
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid invoice property');
        }
        $this->$method($value);
    }

    public function __get($name) {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid invoice property');
        }
        return $this->$method();
    }

    public function setOptions(array $options) {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    function getId() {
        return $this->id;
    }

    function getIngresos() {
        return $this->ingresos;
    }

    function getNombre() {
        return $this->nombre;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setIngresos($ingresos) {
        $this->ingresos = $ingresos;
    }

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    function getCostos() {
        return $this->costos;
    }

    function setCostos($costos) {
        $this->costos = $costos;
    }

}
