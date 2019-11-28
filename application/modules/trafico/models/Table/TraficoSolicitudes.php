<?php

class Trafico_Model_Table_TraficoSolicitudes {

    protected $id;
    protected $idCliente;
    protected $idAduana;
    protected $idUsuario;
    protected $tipoOperacion;
    protected $pedimento;
    protected $referencia;
    protected $creado;
    protected $esquema;
    protected $generada;
    protected $enviada;
    protected $autorizada;
    protected $aprobada;
    protected $tramite;
    protected $tramitada;
    protected $deposito;
    protected $depositado;
    protected $actualizada;
    protected $complemento;
    protected $activa;
    protected $borrada;

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

    function getIdAduana() {
        return $this->idAduana;
    }

    function getIdUsuario() {
        return $this->idUsuario;
    }

    function getTipoOperacion() {
        return $this->tipoOperacion;
    }

    function getPedimento() {
        return $this->pedimento;
    }

    function getReferencia() {
        return $this->referencia;
    }

    function getCreado() {
        return $this->creado;
    }

    function getGenerada() {
        return $this->generada;
    }

    function getEnviada() {
        return $this->enviada;
    }

    function getAutorizada() {
        return $this->autorizada;
    }

    function getAprobada() {
        return $this->aprobada;
    }

    function getTramite() {
        return $this->tramite;
    }

    function getTramitada() {
        return $this->tramitada;
    }

    function getDeposito() {
        return $this->deposito;
    }

    function getDepositado() {
        return $this->depositado;
    }

    function getActualizada() {
        return $this->actualizada;
    }

    function getComplemento() {
        return $this->complemento;
    }

    function getActiva() {
        return $this->activa;
    }

    function getBorrada() {
        return $this->borrada;
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

    function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    function setTipoOperacion($tipoOperacion) {
        $this->tipoOperacion = $tipoOperacion;
    }

    function setPedimento($pedimento) {
        $this->pedimento = $pedimento;
    }

    function setReferencia($referencia) {
        $this->referencia = $referencia;
    }

    function setCreado($creado) {
        $this->creado = $creado;
    }

    function setGenerada($generada) {
        $this->generada = $generada;
    }

    function setEnviada($enviada) {
        $this->enviada = $enviada;
    }

    function setAutorizada($autorizada) {
        $this->autorizada = $autorizada;
    }

    function setAprobada($aprobada) {
        $this->aprobada = $aprobada;
    }

    function setTramite($tramite) {
        $this->tramite = $tramite;
    }

    function setTramitada($tramitada) {
        $this->tramitada = $tramitada;
    }

    function setDeposito($deposito) {
        $this->deposito = $deposito;
    }

    function setDepositado($depositado) {
        $this->depositado = $depositado;
    }

    function setActualizada($actualizada) {
        $this->actualizada = $actualizada;
    }

    function setComplemento($complemento) {
        $this->complemento = $complemento;
    }

    function setActiva($activa) {
        $this->activa = $activa;
    }

    function setBorrada($borrada) {
        $this->borrada = $borrada;
    }

    function getEsquema() {
        return $this->esquema;
    }

    function setEsquema($esquema) {
        $this->esquema = $esquema;
    }

}
