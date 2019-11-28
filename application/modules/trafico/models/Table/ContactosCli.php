<?php

class Trafico_Model_Table_ContactosCli {

    protected $id;
    protected $idCliente;
    protected $idPlanta;
    protected $nombre;
    protected $email;
    protected $tipoContacto;
    protected $aviso;
    protected $pedimento;
    protected $cruce;
    protected $creado;
    protected $creadoPor;
    protected $modificado;
    protected $modificadoPor;

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

    function getIdCliente() {
        return $this->idCliente;
    }

    function getNombre() {
        return $this->nombre;
    }

    function getEmail() {
        return $this->email;
    }

    function getTipoContacto() {
        return $this->tipoContacto;
    }

    function getAviso() {
        return $this->aviso;
    }

    function getPedimento() {
        return $this->pedimento;
    }

    function getCruce() {
        return $this->cruce;
    }

    function getCreado() {
        return $this->creado;
    }

    function getCreadoPor() {
        return $this->creadoPor;
    }

    function getModificado() {
        return $this->modificado;
    }

    function getModificadoPor() {
        return $this->modificadoPor;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setIdCliente($idCliente) {
        $this->idCliente = $idCliente;
    }

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    function setEmail($email) {
        $this->email = $email;
    }

    function setTipoContacto($tipoContacto) {
        $this->tipoContacto = $tipoContacto;
    }

    function setAviso($aviso) {
        $this->aviso = $aviso;
    }

    function setPedimento($pedimento) {
        $this->pedimento = $pedimento;
    }

    function setCruce($cruce) {
        $this->cruce = $cruce;
    }

    function setCreado($creado) {
        $this->creado = $creado;
    }

    function setCreadoPor($creadoPor) {
        $this->creadoPor = $creadoPor;
    }

    function setModificado($modificado) {
        $this->modificado = $modificado;
    }

    function setModificadoPor($modificadoPor) {
        $this->modificadoPor = $modificadoPor;
    }
    
    function getIdPlanta() {
        return $this->idPlanta;
    }

    function setIdPlanta($idPlanta) {
        $this->idPlanta = $idPlanta;
    }

}
