<?php

class Vucem_Model_Table_VucemClientes {

    protected $id;
    protected $patente;
    protected $aduana;
    protected $cvecte;
    protected $identificador;
    protected $rfc;
    protected $razon_soc;
    protected $calle;
    protected $numext;
    protected $numint;
    protected $colonia;
    protected $localidad;
    protected $cp;
    protected $ciudad;
    protected $municipio;
    protected $estado;
    protected $pais;
    protected $creadopor;
    protected $modificadopor;
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
            throw new Exception("Invalid property");
        }
        $this->$method($value);
    }

    public function __get($name) {
        $method = "get" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid property");
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

    function getCvecte() {
        return $this->cvecte;
    }

    function getIdentificador() {
        return $this->identificador;
    }

    function getRfc() {
        return $this->rfc;
    }

    function getRazon_soc() {
        return $this->razon_soc;
    }

    function getCalle() {
        return $this->calle;
    }

    function getNumext() {
        return $this->numext;
    }

    function getNumint() {
        return $this->numint;
    }

    function getColonia() {
        return $this->colonia;
    }

    function getLocalidad() {
        return $this->localidad;
    }

    function getCp() {
        return $this->cp;
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

    function getPais() {
        return $this->pais;
    }

    function getCreadopor() {
        return $this->creadopor;
    }

    function getModificadopor() {
        return $this->modificadopor;
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

    function setPatente($patente) {
        $this->patente = $patente;
    }

    function setAduana($aduana) {
        $this->aduana = $aduana;
    }

    function setCvecte($cvecte) {
        $this->cvecte = $cvecte;
    }

    function setIdentificador($identificador) {
        $this->identificador = $identificador;
    }

    function setRfc($rfc) {
        $this->rfc = $rfc;
    }

    function setRazon_soc($razon_soc) {
        $this->razon_soc = $razon_soc;
    }

    function setCalle($calle) {
        $this->calle = $calle;
    }

    function setNumext($numext) {
        $this->numext = $numext;
    }

    function setNumint($numint) {
        $this->numint = $numint;
    }

    function setColonia($colonia) {
        $this->colonia = $colonia;
    }

    function setLocalidad($localidad) {
        $this->localidad = $localidad;
    }

    function setCp($cp) {
        $this->cp = $cp;
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

    function setPais($pais) {
        $this->pais = $pais;
    }

    function setCreadopor($creadopor) {
        $this->creadopor = $creadopor;
    }

    function setModificadopor($modificadopor) {
        $this->modificadopor = $modificadopor;
    }

    function setCreado($creado) {
        $this->creado = $creado;
    }

    function setModificado($modificado) {
        $this->modificado = $modificado;
    }

}
