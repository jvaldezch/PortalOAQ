<?php

class Automatizacion_Model_Table_ArchivosValidacionPedimentos {

    protected $id;
    protected $idArchivoValidacion;
    protected $archivoNombre;
    protected $patente;
    protected $aduana;
    protected $pedimento;
    protected $tipoMovimiento;
    protected $pedimentoDesistir;
    protected $cveDoc;
    protected $rfcCliente;
    protected $rfcSociedad;
    protected $curpAgente;
    protected $fechaEntrada;
    protected $fechaPago;
    protected $fechaExtraccion;
    protected $fechaPresentacion;
    protected $fechaUsaCan;
    protected $fechaOriginal;
    protected $firma;
    protected $firmaBanco;
    protected $firmaDigital;
    protected $consolidado;
    protected $remesa;
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

    function getArchivoNombre() {
        return $this->archivoNombre;
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

    function getTipoMovimiento() {
        return $this->tipoMovimiento;
    }

    function getPedimentoDesistir() {
        return $this->pedimentoDesistir;
    }

    function getCveDoc() {
        return $this->cveDoc;
    }

    function getRfcCliente() {
        return $this->rfcCliente;
    }

    function getRfcSociedad() {
        return $this->rfcSociedad;
    }

    function getCurpAgente() {
        return $this->curpAgente;
    }

    function getFechaEntrada() {
        return $this->fechaEntrada;
    }

    function getFechaPago() {
        return $this->fechaPago;
    }

    function getFechaExtraccion() {
        return $this->fechaExtraccion;
    }

    function getFechaPresentacion() {
        return $this->fechaPresentacion;
    }

    function getFechaUsaCan() {
        return $this->fechaUsaCan;
    }

    function getFechaOriginal() {
        return $this->fechaOriginal;
    }

    function getFirma() {
        return $this->firma;
    }

    function getFirmaBanco() {
        return $this->firmaBanco;
    }

    function getFirmaDigital() {
        return $this->firmaDigital;
    }

    function getConsolidado() {
        return $this->consolidado;
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

    function setArchivoNombre($archivoNombre) {
        $this->archivoNombre = $archivoNombre;
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

    function setTipoMovimiento($tipoMovimiento) {
        $this->tipoMovimiento = $tipoMovimiento;
    }

    function setPedimentoDesistir($pedimentoDesistir) {
        $this->pedimentoDesistir = $pedimentoDesistir;
    }

    function setCveDoc($cveDoc) {
        $this->cveDoc = $cveDoc;
    }

    function setRfcCliente($rfcCliente) {
        $this->rfcCliente = $rfcCliente;
    }

    function setRfcSociedad($rfcSociedad) {
        $this->rfcSociedad = $rfcSociedad;
    }

    function setCurpAgente($curpAgente) {
        $this->curpAgente = $curpAgente;
    }

    function setFechaEntrada($fechaEntrada) {
        $this->fechaEntrada = $fechaEntrada;
    }

    function setFechaPago($fechaPago) {
        $this->fechaPago = $fechaPago;
    }

    function setFechaExtraccion($fechaExtraccion) {
        $this->fechaExtraccion = $fechaExtraccion;
    }

    function setFechaPresentacion($fechaPresentacion) {
        $this->fechaPresentacion = $fechaPresentacion;
    }

    function setFechaUsaCan($fechaUsaCan) {
        $this->fechaUsaCan = $fechaUsaCan;
    }

    function setFechaOriginal($fechaOriginal) {
        $this->fechaOriginal = $fechaOriginal;
    }

    function setFirma($firma) {
        $this->firma = $firma;
    }

    function setFirmaBanco($firmaBanco) {
        $this->firmaBanco = $firmaBanco;
    }

    function setFirmaDigital($firmaDigital) {
        $this->firmaDigital = $firmaDigital;
    }

    function setConsolidado($consolidado) {
        $this->consolidado = $consolidado;
    }

    function setCreado($creado) {
        $this->creado = $creado;
    }

    function getRemesa() {
        return $this->remesa;
    }

    function setRemesa($remesa) {
        $this->remesa = $remesa;
    }

}
