<?php

class Trafico_Model_Table_Traficos {

    protected $id;
    protected $idCliente;
    protected $idAduana;
    protected $idRepositorio;
    protected $idUsuario;
    protected $patente;
    protected $aduana;
    protected $pedimento;
    protected $pedimentoRectificar;
    protected $referencia;
    protected $rfcCliente;
    protected $consolidado;
    protected $rectificacion;
    protected $tipoCambio;
    protected $pagado;
    protected $regimen;
    protected $cvePedimento;
    protected $ie;
    protected $estatus;
    protected $firmaValidacion;
    protected $firmaBanco;
    protected $creado;
    protected $actualizado;
    protected $idUsuarioModif;

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

    function getIdCliente() {
        return $this->idCliente;
    }

    function getIdAduana() {
        return $this->idAduana;
    }
    
    function getIdRepositorio() {
        return $this->idRepositorio;
    }

    function getIdUsuario() {
        return $this->idUsuario;
    }

    function getPatente() {
        return $this->patente;
    }

    function getAduana() {
        return $this->aduana;
    }

    function getPedimento() {
        return $this->pedimento;
    }

    function getReferencia() {
        return $this->referencia;
    }

    function getRfcCliente() {
        return $this->rfcCliente;
    }

    function getConsolidado() {
        return $this->consolidado;
    }

    function getRectificacion() {
        return $this->rectificacion;
    }

    function getTipoCambio() {
        return $this->tipoCambio;
    }

    function getPagado() {
        return $this->pagado;
    }

    function getRegimen() {
        return $this->regimen;
    }

    function getCvePedimento() {
        return $this->cvePedimento;
    }

    function getIe() {
        return $this->ie;
    }

    function getEstatus() {
        return $this->estatus;
    }

    function getFirmaValidacion() {
        return $this->firmaValidacion;
    }

    function getFirmaBanco() {
        return $this->firmaBanco;
    }

    function getCreado() {
        return $this->creado;
    }

    function getActualizado() {
        return $this->actualizado;
    }

    function getIdUsuarioModif() {
        return $this->idUsuarioModif;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setIdCliente($idCliente) {
        $this->idCliente = $idCliente;
    }

    function setIdAduana($idAduana) {
        $this->idAduana = $idAduana;
    }
    
    function setIdRepositorio($idRepositorio) {
        $this->idRepositorio = $idRepositorio;
    }

    function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    function setPatente($patente) {
        $this->patente = $patente;
    }

    function setAduana($aduana) {
        $this->aduana = $aduana;
    }

    function setPedimento($pedimento) {
        $this->pedimento = $pedimento;
    }

    function setReferencia($referencia) {
        $this->referencia = $referencia;
    }

    function setRfcCliente($rfcCliente) {
        $this->rfcCliente = $rfcCliente;
    }

    function setConsolidado($consolidado) {
        $this->consolidado = $consolidado;
    }

    function setRectificacion($rectificacion) {
        $this->rectificacion = $rectificacion;
    }

    function setTipoCambio($tipoCambio) {
        $this->tipoCambio = $tipoCambio;
    }

    function setPagado($pagado) {
        $this->pagado = $pagado;
    }

    function setRegimen($regimen) {
        $this->regimen = $regimen;
    }

    function setCvePedimento($cvePedimento) {
        $this->cvePedimento = $cvePedimento;
    }

    function setIe($ie) {
        $this->ie = $ie;
    }

    function setEstatus($estatus) {
        $this->estatus = $estatus;
    }

    function setFirmaValidacion($firmaValidacion) {
        $this->firmaValidacion = $firmaValidacion;
    }

    function setFirmaBanco($firmaBanco) {
        $this->firmaBanco = $firmaBanco;
    }

    function setCreado($creado) {
        $this->creado = $creado;
    }

    function setActualizado($actualizado) {
        $this->actualizado = $actualizado;
    }

    function setIdUsuarioModif($idUsuarioModif) {
        $this->idUsuarioModif = $idUsuarioModif;
    }
    
    function getPedimentoRectificar() {
        return $this->pedimentoRectificar;
    }

    function setPedimentoRectificar($pedimentoRectificar) {
        $this->pedimentoRectificar = $pedimentoRectificar;
    }

}
