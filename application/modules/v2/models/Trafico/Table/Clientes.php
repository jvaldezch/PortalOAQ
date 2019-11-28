<?php

class V2_Model_Trafico_Table_Clientes {

    protected $id;
    protected $nombre;
    protected $rfc;
    protected $peca;
    protected $esquema;
    protected $activo;
    protected $creado;
    protected $usuario;

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

    function getNombre() {
        return $this->nombre;
    }

    function getRfc() {
        return $this->rfc;
    }

    function getPeca() {
        return $this->peca;
    }

    function getEsquema() {
        return $this->esquema;
    }

    function getActivo() {
        return $this->activo;
    }

    function getCreado() {
        return $this->creado;
    }

    function getUsuario() {
        return $this->usuario;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    function setRfc($rfc) {
        $this->rfc = $rfc;
    }

    function setPeca($peca) {
        $this->peca = $peca;
    }

    function setEsquema($esquema) {
        $this->esquema = $esquema;
    }

    function setActivo($activo) {
        $this->activo = $activo;
    }

    function setCreado($creado) {
        $this->creado = $creado;
    }

    function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

}
