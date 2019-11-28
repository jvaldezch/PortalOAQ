<?php

class Trafico_Model_Table_TraficoSolRepo {

    protected $id;
    protected $idSolicitud;
    protected $idRepositorioConta;

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

    function getIdSolicitud() {
        return $this->idSolicitud;
    }

    function getIdRepositorioConta() {
        return $this->idRepositorioConta;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setIdSolicitud($idSolicitud) {
        $this->idSolicitud = $idSolicitud;
    }

    function setIdRepositorioConta($idRepositorioConta) {
        $this->idRepositorioConta = $idRepositorioConta;
    }

}
