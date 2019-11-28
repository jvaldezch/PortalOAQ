<?php

class Bodega_Form_NuevaEntrada extends Twitter_Bootstrap_Form_Horizontal {

    protected $_clientes;
    protected $_aduanas;
    protected $_aduana;

    public function setClientes($clientes = null) {
        $this->_clientes = $clientes;
    }

    public function init() {

        $decorators = array("ViewHelper", "Errors", "Label",);

        $this->addElement("select", "idCliente", array(
            "class" => "traffic-select-large",
            "multiOptions" => isset($this->_clientes) ? $this->_clientes : array("" => "---"),
            "decorators" => $decorators,
            "validators" => array('NotEmpty', new Zend_Validate_Int())
        ));
        
    }

}
