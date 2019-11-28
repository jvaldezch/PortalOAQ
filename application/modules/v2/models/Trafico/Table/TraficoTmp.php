<?php

class V2_Model_Trafico_Table_TraficoTmp {

    protected $id;
    protected $idAduana;
    protected $idCliente;
    protected $idUsuario;
    protected $pedimento;
    protected $referencia;
    protected $cvePedimento;
    protected $tipoOperacion;
    protected $consolidado;
    protected $rectificacion;
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

    function getIdAduana() {
        return $this->idAduana;
    }

    function getIdCliente() {
        return $this->idCliente;
    }

    function getIdUsuario() {
        return $this->idUsuario;
    }

    function getPedimento() {
        return $this->pedimento;
    }

    function getReferencia() {
        return $this->referencia;
    }

    function getCvePedimento() {
        return $this->cvePedimento;
    }

    function getTipoOperacion() {
        return $this->tipoOperacion;
    }

    function getConsolidado() {
        return $this->consolidado;
    }

    function getRectificacion() {
        return $this->rectificacion;
    }

    function getCreado() {
        return $this->creado;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setIdAduana($idAduana) {
        $this->idAduana = $idAduana;
    }

    function setIdCliente($idCliente) {
        $this->idCliente = $idCliente;
    }

    function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    function setPedimento($pedimento) {
        $this->pedimento = $pedimento;
    }

    function setReferencia($referencia) {
        $this->referencia = $referencia;
    }

    function setCvePedimento($cvePedimento) {
        $this->cvePedimento = $cvePedimento;
    }

    function setTipoOperacion($tipoOperacion) {
        $this->tipoOperacion = $tipoOperacion;
    }

    function setConsolidado($consolidado) {
        $this->consolidado = $consolidado;
    }

    function setRectificacion($rectificacion) {
        $this->rectificacion = $rectificacion;
    }

    function setCreado($creado) {
        $this->creado = $creado;
    }

}
