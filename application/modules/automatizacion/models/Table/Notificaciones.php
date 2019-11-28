<?php

class Automatizacion_Model_Table_Notificaciones {

    protected $id;
    protected $idAduana;
    protected $idTrafico;
    protected $pedimento;
    protected $referencia;
    protected $contenido;
    protected $de;
    protected $para;
    protected $tipo;
    protected $estatus;
    protected $enviado;
    protected $creado;

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

    function getPedimento() {
        return $this->pedimento;
    }

    function getReferencia() {
        return $this->referencia;
    }

    function getContenido() {
        return $this->contenido;
    }

    function getDe() {
        return $this->de;
    }

    function getPara() {
        return $this->para;
    }

    function getTipo() {
        return $this->tipo;
    }

    function getEstatus() {
        return $this->estatus;
    }

    function getEnviado() {
        return $this->enviado;
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

    function setPedimento($pedimento) {
        $this->pedimento = $pedimento;
    }

    function setReferencia($referencia) {
        $this->referencia = $referencia;
    }

    function setContenido($contenido) {
        $this->contenido = $contenido;
    }

    function setDe($de) {
        $this->de = $de;
    }

    function setPara($para) {
        $this->para = $para;
    }

    function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    function setEstatus($estatus) {
        $this->estatus = $estatus;
    }

    function setEnviado($enviado) {
        $this->enviado = $enviado;
    }

    function setCreado($creado) {
        $this->creado = $creado;
    }

    function getIdTrafico() {
        return $this->idTrafico;
    }

    function setIdTrafico($idTrafico) {
        $this->idTrafico = $idTrafico;
    }

}
