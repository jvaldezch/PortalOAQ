<?php

class V2_Model_Table_Usuarios {

    protected $id;
    protected $password;
    protected $status;
    protected $intentos;
    protected $acceso;
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

    function getPassword() {
        return $this->password;
    }

    function getStatus() {
        return $this->status;
    }

    function getIntentos() {
        return $this->intentos;
    }

    function getAcceso() {
        return $this->acceso;
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

    function setPassword($password) {
        $this->password = $password;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setIntentos($intentos) {
        $this->intentos = $intentos;
    }

    function setAcceso($acceso) {
        $this->acceso = $acceso;
    }

    function setCreado($creado) {
        $this->creado = $creado;
    }

    function setActualizado($actualizado) {
        $this->actualizado = $actualizado;
    }

}
