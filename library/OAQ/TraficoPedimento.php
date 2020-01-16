<?php

/**
 * Description of EmailNotifications
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_TraficoPedimento {

    protected $id;
    protected $idTrafico;
    protected $pedimentos;
    protected $_firephp;

    function setIdTrafico($idTrafico) {
        $this->idTrafico = $idTrafico;
    }

    function setId($id) {
        $this->id = $id;
    }

    public function __construct(array $options = null) {
        $this->_firephp = Zend_Registry::get("firephp");
        $this->pedimentos = new Pedimento_Model_Pedimento();
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function buscar($usuario) {
        if ((!$id = $this->pedimentos->buscar($this->idTrafico))) {
            $id = $this->pedimentos->agregar($this->idTrafico, $usuario);
            return $this->pedimentos->obtener($id);
        }
        return $id;
    }

    public function actualizar($arr) {
        if (($this->pedimentos->actualizar($this->id, $arr))) {
            return true;
        }
        return null;
    }

    public function __set($name, $value) {
        $method = "set" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid property " . __METHOD__);
        }
        $this->$method($value);
    }

    public function __get($name) {
        $method = "get" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid property " . __METHOD__);
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

}
