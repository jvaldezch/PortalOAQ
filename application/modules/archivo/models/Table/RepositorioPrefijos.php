<?php

class Archivo_Model_Table_RepositorioPrefijos {

    protected $id;
    protected $idDocumento;
    protected $prefijo;

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

    function getIdDocumento() {
        return $this->idDocumento;
    }

    function getPrefijo() {
        return $this->prefijo;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setIdDocumento($idDocumento) {
        $this->idDocumento = $idDocumento;
    }

    function setPrefijo($prefijo) {
        $this->prefijo = $prefijo;
    }

}
