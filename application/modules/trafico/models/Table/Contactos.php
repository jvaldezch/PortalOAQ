<?php

class Trafico_Model_Table_Contactos {

    protected $id;
    protected $idAduana;
    protected $nombre;
    protected $email;
    protected $tipoContacto;
    protected $creacion;
    protected $deposito;
    protected $comentario;
    protected $cancelacion;
    protected $habilitado;
    protected $creado;
    protected $creadoPor;
    protected $modificado;
    protected $modificadoPor;

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

    function getIdAduana() {
        return $this->idAduana;
    }

    function getNombre() {
        return $this->nombre;
    }

    function getEmail() {
        return $this->email;
    }

    function getTipoContacto() {
        return $this->tipoContacto;
    }

    function getCreacion() {
        return $this->creacion;
    }

    function getDeposito() {
        return $this->deposito;
    }

    function getComentario() {
        return $this->comentario;
    }

    function getCancelacion() {
        return $this->cancelacion;
    }

    function getHabilitado() {
        return $this->habilitado;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setIdAduana($idAduana) {
        $this->idAduana = $idAduana;
    }

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    function setEmail($email) {
        $this->email = $email;
    }

    function setTipoContacto($tipoContacto) {
        $this->tipoContacto = $tipoContacto;
    }

    function setCreacion($creacion) {
        $this->creacion = $creacion;
    }

    function setDeposito($deposito) {
        $this->deposito = $deposito;
    }

    function setComentario($comentario) {
        $this->comentario = $comentario;
    }

    function setCancelacion($cancelacion) {
        $this->cancelacion = $cancelacion;
    }

    function setHabilitado($habilitado) {
        $this->habilitado = $habilitado;
    }

    function getCreado() {
        return $this->creado;
    }

    function getCreadoPor() {
        return $this->creadoPor;
    }

    function getModificado() {
        return $this->modificado;
    }

    function getModificadoPor() {
        return $this->modificadoPor;
    }

    function setCreado($creado) {
        $this->creado = $creado;
    }

    function setCreadoPor($creadoPor) {
        $this->creadoPor = $creadoPor;
    }

    function setModificado($modificado) {
        $this->modificado = $modificado;
    }

    function setModificadoPor($modificadoPor) {
        $this->modificadoPor = $modificadoPor;
    }

}
