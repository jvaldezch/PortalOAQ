<?php

class Webservice_Model_Table_WsTokens {

    protected $id;
    protected $rfc;
    protected $token;
    protected $activo;
    protected $creado;
    protected $modificado;

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

    function getRfc() {
        return $this->rfc;
    }

    function getToken() {
        return $this->token;
    }

    function getActivo() {
        return $this->activo;
    }

    function getCreado() {
        return $this->creado;
    }

    function getModificado() {
        return $this->modificado;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setRfc($rfc) {
        $this->rfc = $rfc;
    }

    function setToken($token) {
        $this->token = $token;
    }

    function setActivo($activo) {
        $this->activo = $activo;
    }

    function setCreado($creado) {
        $this->creado = $creado;
    }

    function setModificado($modificado) {
        $this->modificado = $modificado;
    }

}
