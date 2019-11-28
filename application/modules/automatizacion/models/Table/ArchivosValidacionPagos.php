<?php

class Automatizacion_Model_Table_ArchivosValidacionPagos {

    protected $id;
    protected $idArchivoValidacion;
    protected $patente;
    protected $aduana;
    protected $pedimento;
    protected $rfcImportador;
    protected $caja;
    protected $numOperacion;
    protected $firmaBanco;
    protected $error;
    protected $fecha;
    protected $hora;
    protected $fechaPago;
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

    function getAduana() {
        return $this->aduana;
    }

    function getPedimento() {
        return $this->pedimento;
    }

    function getRfcImportador() {
        return $this->rfcImportador;
    }

    function getCaja() {
        return $this->caja;
    }

    function getNumOperacion() {
        return $this->numOperacion;
    }

    function getFirmaBanco() {
        return $this->firmaBanco;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getHora() {
        return $this->hora;
    }

    function getFechaPago() {
        return $this->fechaPago;
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

    function setAduana($aduana) {
        $this->aduana = $aduana;
    }

    function setPedimento($pedimento) {
        $this->pedimento = $pedimento;
    }

    function setRfcImportador($rfcImportador) {
        $this->rfcImportador = $rfcImportador;
    }

    function setCaja($caja) {
        $this->caja = $caja;
    }

    function setNumOperacion($numOperacion) {
        $this->numOperacion = $numOperacion;
    }

    function setFirmaBanco($firmaBanco) {
        $this->firmaBanco = $firmaBanco;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setHora($hora) {
        $this->hora = $hora;
    }

    function setFechaPago($fechaPago) {
        $this->fechaPago = $fechaPago;
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

}
