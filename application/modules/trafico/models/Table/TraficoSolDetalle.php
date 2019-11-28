<?php

class Trafico_Model_Table_TraficoSolDetalle {

    protected $id;
    protected $idSolicitud;
    protected $idAduana;
    protected $cvePed;
    protected $fechaArribo;
    protected $fechaAlmacenaje;
    protected $fechaEta;
    protected $tipoFacturacion;
    protected $tipoCarga;
    protected $bl;
    protected $peso;
    protected $numFactura;
    protected $valorMercancia;
    protected $peca;
    protected $banco;
    protected $almacen;
    protected $mercancia;
    protected $creado;
    protected $actualizado;

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

    function getIdSolicitud() {
        return $this->idSolicitud;
    }

    function getIdAduana() {
        return $this->idAduana;
    }

    function getCvePed() {
        return $this->cvePed;
    }

    function getFechaArribo() {
        return $this->fechaArribo;
    }

    function getFechaAlmacenaje() {
        return $this->fechaAlmacenaje;
    }

    function getFechaEta() {
        return $this->fechaEta;
    }

    function getTipoFacturacion() {
        return $this->tipoFacturacion;
    }

    function getTipoCarga() {
        return $this->tipoCarga;
    }

    function getBl() {
        return $this->bl;
    }

    function getPeso() {
        return $this->peso;
    }

    function getNumFactura() {
        return $this->numFactura;
    }

    function getValorMercancia() {
        return $this->valorMercancia;
    }

    function getPeca() {
        return $this->peca;
    }

    function getBanco() {
        return $this->banco;
    }

    function getAlmacen() {
        return $this->almacen;
    }

    function getMercancia() {
        return $this->mercancia;
    }

    function getCreado() {
        return $this->creado;
    }

    function getActualizado() {
        return $this->actualizado;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setIdSolicitud($idSolicitud) {
        $this->idSolicitud = $idSolicitud;
    }

    function setIdAduana($idAduana) {
        $this->idAduana = $idAduana;
    }

    function setCvePed($cvePed) {
        $this->cvePed = $cvePed;
    }

    function setFechaArribo($fechaArribo) {
        $this->fechaArribo = $fechaArribo;
    }

    function setFechaAlmacenaje($fechaAlmacenaje) {
        $this->fechaAlmacenaje = $fechaAlmacenaje;
    }

    function setFechaEta($fechaEta) {
        $this->fechaEta = $fechaEta;
    }

    function setTipoFacturacion($tipoFacturacion) {
        $this->tipoFacturacion = $tipoFacturacion;
    }

    function setTipoCarga($tipoCarga) {
        $this->tipoCarga = $tipoCarga;
    }

    function setBl($bl) {
        $this->bl = $bl;
    }

    function setPeso($peso) {
        $this->peso = $peso;
    }

    function setNumFactura($numFactura) {
        $this->numFactura = $numFactura;
    }

    function setValorMercancia($valorMercancia) {
        $this->valorMercancia = $valorMercancia;
    }

    function setPeca($peca) {
        $this->peca = $peca;
    }

    function setBanco($banco) {
        $this->banco = $banco;
    }

    function setAlmacen($almacen) {
        $this->almacen = $almacen;
    }

    function setMercancia($mercancia) {
        $this->mercancia = $mercancia;
    }

    function setCreado($creado) {
        $this->creado = $creado;
    }

    function setActualizado($actualizado) {
        $this->actualizado = $actualizado;
    }

}
