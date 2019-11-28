<?php

class Archivo_Model_Table_ChecklistReferencias {

    protected $id;
    protected $idTrafico;
    protected $patente;
    protected $aduana;
    protected $pedimento;
    protected $referencia;
    protected $checklist;
    protected $revision;
    protected $observaciones;
    protected $revisionOperaciones;
    protected $fechaRevisionOperaciones;
    protected $revisionAdministracion;
    protected $fechaRevisionAdministracion;
    protected $completo;
    protected $fechaCompleto;
    protected $creado;
    protected $actualizado;

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

    function getChecklist() {
        return $this->checklist;
    }

    function getCompleto() {
        return $this->completo;
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

    function setIdTrafico($idTrafico) {
        $this->idTrafico = $idTrafico;
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

    function setChecklist($checklist) {
        $this->checklist = $checklist;
    }

    function setCompleto($completo) {
        $this->completo = $completo;
    }

    function setCreado($creado) {
        $this->creado = $creado;
    }

    function setActualizado($actualizado) {
        $this->actualizado = $actualizado;
    }

    function getObservaciones() {
        return $this->observaciones;
    }

    function setObservaciones($observaciones) {
        $this->observaciones = $observaciones;
    }

    function getRevision() {
        return $this->revision;
    }

    function setRevision($revision) {
        $this->revision = $revision;
    }

    function getRevisionOperaciones() {
        return $this->revisionOperaciones;
    }

    function getRevisionAdministracion() {
        return $this->revisionAdministracion;
    }

    function setRevisionOperaciones($revisionOperaciones) {
        $this->revisionOperaciones = $revisionOperaciones;
    }

    function setRevisionAdministracion($revisionAdministracion) {
        $this->revisionAdministracion = $revisionAdministracion;
    }

    function getFechaRevisionOperaciones() {
        return $this->fechaRevisionOperaciones;
    }

    function getFechaRevisionAdministracion() {
        return $this->fechaRevisionAdministracion;
    }

    function getFechaCompleto() {
        return $this->fechaCompleto;
    }

    function setFechaRevisionOperaciones($fechaRevisionOperaciones) {
        $this->fechaRevisionOperaciones = $fechaRevisionOperaciones;
    }

    function setFechaRevisionAdministracion($fechaRevisionAdministracion) {
        $this->fechaRevisionAdministracion = $fechaRevisionAdministracion;
    }

    function setFechaCompleto($fechaCompleto) {
        $this->fechaCompleto = $fechaCompleto;
    }

}
