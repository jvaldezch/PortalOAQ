<?php

class Archivo_Model_RepositorioTemporal {

    protected $id;
    protected $idTrafico;
    protected $idMensaje;
    protected $idComentario;
    protected $patente;
    protected $aduana;
    protected $tipoArchivo;
    protected $subTipoArchivo;
    protected $pedimento;
    protected $referencia;
    protected $rfcCliente;
    protected $nombreArchivo;
    protected $archivo;
    protected $ubicacion;
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

    function getTipoArchivo() {
        return $this->tipoArchivo;
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

    function getArchivo() {
        return $this->archivo;
    }

    function getUbicacion() {
        return $this->ubicacion;
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

    function setPatente($patente) {
        $this->patente = $patente;
    }

    function setAduana($aduana) {
        $this->aduana = $aduana;
    }

    function setTipoArchivo($tipoArchivo) {
        $this->tipoArchivo = $tipoArchivo;
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

    function setArchivo($archivo) {
        $this->archivo = $archivo;
    }

    function setUbicacion($ubicacion) {
        $this->ubicacion = $ubicacion;
    }

    function setCreado($creado) {
        $this->creado = $creado;
    }

    function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    function getIdTrafico() {
        return $this->idTrafico;
    }

    function setIdTrafico($idTrafico) {
        $this->idTrafico = $idTrafico;
    }

    function getNombreArchivo() {
        return $this->nombreArchivo;
    }

    function setNombreArchivo($nombreArchivo) {
        $this->nombreArchivo = $nombreArchivo;
    }

    function getIdMensaje() {
        return $this->idMensaje;
    }

    function getIdComentario() {
        return $this->idComentario;
    }

    function setIdMensaje($idMensaje) {
        $this->idMensaje = $idMensaje;
    }

    function setIdComentario($idComentario) {
        $this->idComentario = $idComentario;
    }

    function getSubTipoArchivo() {
        return $this->subTipoArchivo;
    }

    function setSubTipoArchivo($subTipoArchivo) {
        $this->subTipoArchivo = $subTipoArchivo;
    }

}
