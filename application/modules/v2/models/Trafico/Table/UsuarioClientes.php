<?php

class V2_Model_Trafico_Table_UsuarioAduanas {

    protected $id;
    protected $idUsuario;
    protected $idCliente;
    protected $idAduana;

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

    function getIdUsuario() {
        return $this->idUsuario;
    }

    function getIdCliente() {
        return $this->idCliente;
    }

    function getIdAduana() {
        return $this->idAduana;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    function setIdCliente($idCliente) {
        $this->idCliente = $idCliente;
    }

    function setIdAduana($idAduana) {
        $this->idAduana = $idAduana;
    }

}
