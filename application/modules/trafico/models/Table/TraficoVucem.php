<?php

class Trafico_Model_Table_TraficoVucem {

    protected $id;
    protected $idTrafico;
    protected $idFactura;
    protected $numFactura;
    protected $nombreArchivo;
    protected $tipoDocumento;
    protected $descripcionDocumento;
    protected $instruccion;
    protected $solicitud;
    protected $edoc;
    protected $enviar;
    protected $error;
    protected $enviado;
    protected $respuesta;
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

    function getIdTrafico() {
        return $this->idTrafico;
    }

    function getIdFactura() {
        return $this->idFactura;
    }

    function getNombreArchivo() {
        return $this->nombreArchivo;
    }

    function getTipoDocumento() {
        return $this->tipoDocumento;
    }

    function getDescripcionDocumento() {
        return $this->descripcionDocumento;
    }

    function getInstruccion() {
        return $this->instruccion;
    }

    function getSolicitud() {
        return $this->solicitud;
    }

    function getEdoc() {
        return $this->edoc;
    }

    function getEnviar() {
        return $this->enviar;
    }

    function getError() {
        return $this->error;
    }

    function getEnviado() {
        return $this->enviado;
    }

    function getRespuesta() {
        return $this->respuesta;
    }

    function getCreado() {
        return $this->creado;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setIdTrafico($idTrafico) {
        $this->idTrafico = $idTrafico;
    }

    function setIdFactura($idFactura) {
        $this->idFactura = $idFactura;
    }

    function setNombreArchivo($nombreArchivo) {
        $this->nombreArchivo = $nombreArchivo;
    }

    function setTipoDocumento($tipoDocumento) {
        $this->tipoDocumento = $tipoDocumento;
    }

    function setDescripcionDocumento($descripcionDocumento) {
        $this->descripcionDocumento = $descripcionDocumento;
    }

    function setInstruccion($instruccion) {
        $this->instruccion = $instruccion;
    }

    function setSolicitud($solicitud) {
        $this->solicitud = $solicitud;
    }

    function setEdoc($edoc) {
        $this->edoc = $edoc;
    }

    function setEnviar($enviar) {
        $this->enviar = $enviar;
    }

    function setError($error) {
        $this->error = $error;
    }

    function setEnviado($enviado) {
        $this->enviado = $enviado;
    }

    function setRespuesta($respuesta) {
        $this->respuesta = $respuesta;
    }

    function setCreado($creado) {
        $this->creado = $creado;
    }

    function getNumFactura() {
        return $this->numFactura;
    }

    function setNumFactura($numFactura) {
        $this->numFactura = $numFactura;
    }

}
