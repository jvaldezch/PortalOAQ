<?php

class Trafico_Model_Table_TraficoCliDom {

    protected $id;
    protected $idCliente;
    protected $clave;
    protected $identificador;
    protected $nombre;
    protected $calle;
    protected $numExt;
    protected $numInt;
    protected $colonia;
    protected $localidad;
    protected $ciudad;
    protected $municipio;
    protected $estado;
    protected $codigoPostal;
    protected $pais;
    protected $creado;
    protected $modificado;

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

    function getIdCliente() {
        return $this->idCliente;
    }

    function getClave() {
        return $this->clave;
    }

    function getIdentificador() {
        return $this->identificador;
    }

    function getNombre() {
        return $this->nombre;
    }

    function getCalle() {
        return $this->calle;
    }

    function getNumExt() {
        return $this->numExt;
    }

    function getNumInt() {
        return $this->numInt;
    }

    function getColonia() {
        return $this->colonia;
    }

    function getLocalidad() {
        return $this->localidad;
    }

    function getCiudad() {
        return $this->ciudad;
    }

    function getMunicipio() {
        return $this->municipio;
    }

    function getEstado() {
        return $this->estado;
    }

    function getCodigoPostal() {
        return $this->codigoPostal;
    }

    function getPais() {
        return $this->pais;
    }

    function getCreado() {
        return $this->creado;
    }

    function getModificado() {
        return $this->modificado;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setIdCliente($idCliente) {
        $this->idCliente = $idCliente;
    }

    function setClave($clave) {
        $this->clave = $clave;
    }

    function setIdentificador($identificador) {
        $this->identificador = $identificador;
    }

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    function setCalle($calle) {
        $this->calle = $calle;
    }

    function setNumExt($numExt) {
        $this->numExt = $numExt;
    }

    function setNumInt($numInt) {
        $this->numInt = $numInt;
    }

    function setColonia($colonia) {
        $this->colonia = $colonia;
    }

    function setLocalidad($localidad) {
        $this->localidad = $localidad;
    }

    function setCiudad($ciudad) {
        $this->ciudad = $ciudad;
    }

    function setMunicipio($municipio) {
        $this->municipio = $municipio;
    }

    function setEstado($estado) {
        $this->estado = $estado;
    }

    function setCodigoPostal($codigoPostal) {
        $this->codigoPostal = $codigoPostal;
    }

    function setPais($pais) {
        $this->pais = $pais;
    }

    function setCreado($creado) {
        $this->creado = $creado;
    }

    function setModificado($modificado) {
        $this->modificado = $modificado;
    }

}
