<?php

class Automatizacion_Model_Table_EmailsLeidos {

    protected $id;
    protected $idEmail;
    protected $uuidEmail;
    protected $fecha;
    protected $hora;
    protected $asunto;
    protected $de;
    protected $creado;

    public function __construct(array $options = null) {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function __set($name, $value) {
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid property');
        }
        $this->$method($value);
    }

    public function __get($name) {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid property');
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

    function getIdEmail() {
        return $this->idEmail;
    }

    function getUuidEmail() {
        return $this->uuidEmail;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getAsunto() {
        return $this->asunto;
    }

    function getDe() {
        return $this->de;
    }

    function getCreado() {
        return $this->creado;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setIdEmail($idEmail) {
        $this->idEmail = $idEmail;
    }

    function setUuidEmail($uuidEmail) {
        $this->uuidEmail = $uuidEmail;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setAsunto($asunto) {
        $this->asunto = $asunto;
    }

    function setDe($de) {
        $this->de = $de;
    }

    function setCreado($creado) {
        $this->creado = $creado;
    }

    function getHora() {
        return $this->hora;
    }

    function setHora($hora) {
        $this->hora = $hora;
    }

}
