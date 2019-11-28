<?php

class Archivo_Model_Table_Repositorio {

    protected $id;
    protected $rfc_cliente;
    protected $tipo_archivo;
    protected $sub_tipo_archivo;
    protected $referencia;
    protected $patente;
    protected $aduana;
    protected $pedimento;
    protected $uuid;
    protected $folio;
    protected $fecha;
    protected $emisor_rfc;
    protected $emisor_nombre;
    protected $receptor_rfc;
    protected $receptor_nombre;
    protected $nom_archivo;
    protected $ubicacion;
    protected $ubicacion_xml;
    protected $ubicacion_pdf;
    protected $edocument;
    protected $observaciones;
    protected $email;
    protected $cofidi;
    protected $ftp;
    protected $borrado;
    protected $creado;
    protected $usuario;
    protected $modificado;
    protected $modificadoPor;

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

    function getRfc_cliente() {
        return $this->rfc_cliente;
    }

    function getTipo_archivo() {
        return $this->tipo_archivo;
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

    function getFolio() {
        return $this->folio;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getEmisor_rfc() {
        return $this->emisor_rfc;
    }

    function getEmisor_nombre() {
        return $this->emisor_nombre;
    }

    function getReceptor_rfc() {
        return $this->receptor_rfc;
    }

    function getReceptor_nombre() {
        return $this->receptor_nombre;
    }

    function getNom_archivo() {
        return $this->nom_archivo;
    }

    function getUbicacion() {
        return $this->ubicacion;
    }

    function getUbicacion_xml() {
        return $this->ubicacion_xml;
    }

    function getUbicacion_pdf() {
        return $this->ubicacion_pdf;
    }

    function getEdocument() {
        return $this->edocument;
    }

    function getObservaciones() {
        return $this->observaciones;
    }

    function getEmail() {
        return $this->email;
    }

    function getCofidi() {
        return $this->cofidi;
    }

    function getFtp() {
        return $this->ftp;
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

    function getModificado() {
        return $this->modificado;
    }

    function getModificadoPor() {
        return $this->modificadoPor;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setRfc_cliente($rfc_cliente) {
        $this->rfc_cliente = $rfc_cliente;
    }

    function setTipo_archivo($tipo_archivo) {
        $this->tipo_archivo = $tipo_archivo;
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

    function setFolio($folio) {
        $this->folio = $folio;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setEmisor_rfc($emisor_rfc) {
        $this->emisor_rfc = $emisor_rfc;
    }

    function setEmisor_nombre($emisor_nombre) {
        $this->emisor_nombre = $emisor_nombre;
    }

    function setReceptor_rfc($receptor_rfc) {
        $this->receptor_rfc = $receptor_rfc;
    }

    function setReceptor_nombre($receptor_nombre) {
        $this->receptor_nombre = $receptor_nombre;
    }

    function setNom_archivo($nom_archivo) {
        $this->nom_archivo = $nom_archivo;
    }

    function setUbicacion($ubicacion) {
        $this->ubicacion = $ubicacion;
    }

    function setUbicacion_xml($ubicacion_xml) {
        $this->ubicacion_xml = $ubicacion_xml;
    }

    function setUbicacion_pdf($ubicacion_pdf) {
        $this->ubicacion_pdf = $ubicacion_pdf;
    }

    function setEdocument($edocument) {
        $this->edocument = $edocument;
    }

    function setObservaciones($observaciones) {
        $this->observaciones = $observaciones;
    }

    function setEmail($email) {
        $this->email = $email;
    }

    function setCofidi($cofidi) {
        $this->cofidi = $cofidi;
    }

    function setFtp($ftp) {
        $this->ftp = $ftp;
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

    function setModificado($modificado) {
        $this->modificado = $modificado;
    }

    function setModificadoPor($modificadoPor) {
        $this->modificadoPor = $modificadoPor;
    }
    
    function getSub_tipo_archivo() {
        return $this->sub_tipo_archivo;
    }

    function setSub_tipo_archivo($sub_tipo_archivo) {
        $this->sub_tipo_archivo = $sub_tipo_archivo;
    }


}
