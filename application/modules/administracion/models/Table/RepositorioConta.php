<?php

class Administracion_Model_Table_RepositorioConta {

    protected $id;
    protected $idSolicitud;
    protected $rfcCliente;
    protected $nombreCliente;
    protected $tipoPoliza;
    protected $tipoArchivo;
    protected $poliza;
    protected $folio;
    protected $fecha;
    protected $factura;
    protected $transferencia;
    protected $referencia;
    protected $patente;
    protected $aduana;
    protected $pedimento;
    protected $uuid;
    protected $hash;
    protected $rfcEmisor;
    protected $nombreEmisor;
    protected $rfcReceptor;
    protected $nombreReceptor;
    protected $total;
    protected $nombreArchivo;
    protected $ubicacion;
    protected $observaciones;
    protected $cfdiValido;
    protected $version;
    protected $borrado;
    protected $creado;
    protected $usuario;

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

    function getRfcCliente() {
        return $this->rfcCliente;
    }

    function getNombreCliente() {
        return $this->nombreCliente;
    }

    function getTipoPoliza() {
        return $this->tipoPoliza;
    }

    function getTipoArchivo() {
        return $this->tipoArchivo;
    }

    function getPoliza() {
        return $this->poliza;
    }

    function getFolio() {
        return $this->folio;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getFactura() {
        return $this->factura;
    }

    function getTransferencia() {
        return $this->transferencia;
    }

    function getReferencia() {
        return $this->referencia;
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

    function getUuid() {
        return $this->uuid;
    }

    function getRfcEmisor() {
        return $this->rfcEmisor;
    }

    function getNombreEmisor() {
        return $this->nombreEmisor;
    }

    function getRfcReceptor() {
        return $this->rfcReceptor;
    }

    function getNombreReceptor() {
        return $this->nombreReceptor;
    }

    function getTotal() {
        return $this->total;
    }

    function getNombreArchivo() {
        return $this->nombreArchivo;
    }

    function getUbicacion() {
        return $this->ubicacion;
    }

    function getObservaciones() {
        return $this->observaciones;
    }

    function getCfdiValido() {
        return $this->cfdiValido;
    }

    function getVersion() {
        return $this->version;
    }

    function getBorrado() {
        return $this->borrado;
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

    function setRfcCliente($rfcCliente) {
        $this->rfcCliente = $rfcCliente;
    }

    function setNombreCliente($nombreCliente) {
        $this->nombreCliente = $nombreCliente;
    }

    function setTipoPoliza($tipoPoliza) {
        $this->tipoPoliza = $tipoPoliza;
    }

    function setTipoArchivo($tipoArchivo) {
        $this->tipoArchivo = $tipoArchivo;
    }

    function setPoliza($poliza) {
        $this->poliza = $poliza;
    }

    function setFolio($folio) {
        $this->folio = $folio;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setFactura($factura) {
        $this->factura = $factura;
    }

    function setTransferencia($transferencia) {
        $this->transferencia = $transferencia;
    }

    function setReferencia($referencia) {
        $this->referencia = $referencia;
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

    function setUuid($uuid) {
        $this->uuid = $uuid;
    }

    function setRfcEmisor($rfcEmisor) {
        $this->rfcEmisor = $rfcEmisor;
    }

    function setNombreEmisor($nombreEmisor) {
        $this->nombreEmisor = $nombreEmisor;
    }

    function setRfcReceptor($rfcReceptor) {
        $this->rfcReceptor = $rfcReceptor;
    }

    function setNombreReceptor($nombreReceptor) {
        $this->nombreReceptor = $nombreReceptor;
    }

    function setTotal($total) {
        $this->total = $total;
    }

    function setNombreArchivo($nombreArchivo) {
        $this->nombreArchivo = $nombreArchivo;
    }

    function setUbicacion($ubicacion) {
        $this->ubicacion = $ubicacion;
    }

    function setObservaciones($observaciones) {
        $this->observaciones = $observaciones;
    }

    function setCfdiValido($cfdiValido) {
        $this->cfdiValido = $cfdiValido;
    }

    function setVersion($version) {
        $this->version = $version;
    }

    function setBorrado($borrado) {
        $this->borrado = $borrado;
    }

    function setCreado($creado) {
        $this->creado = $creado;
    }

    function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    function getHash() {
        return $this->hash;
    }

    function setHash($hash) {
        $this->hash = $hash;
    }

    function getIdSolicitud() {
        return $this->idSolicitud;
    }

    function setIdSolicitud($idSolicitud) {
        $this->idSolicitud = $idSolicitud;
    }

}
