<?php

class Bodega_Form_CrearTrafico extends Twitter_Bootstrap_Form_Horizontal {

    protected $_clientes;
    protected $_bodegas;

    public function setClientes($clientes = null) {
        $this->_clientes = $clientes;
    }

    public function setBodegas($bodegas = null) {
        $this->_bodegas = $bodegas;
    }

    public function init() {

        $decorators = array("ViewHelper", "Errors", "Label",);

        $this->addElement("select", "idBodega", array(
            "class" => "traffic-select-large",
            "multiOptions" => isset($this->_bodegas) ? $this->_bodegas : array("" => "---"),
            "attribs" => array("tabindex" => "1"),
            "decorators" => $decorators,
            "validators" => array('NotEmpty', new Zend_Validate_Int())
        ));

        $this->addElement("select", "idCliente", array(
            "class" => "traffic-select-large",
            "multiOptions" => isset($this->_clientes) ? $this->_clientes : array("" => "---"),
            "attribs" => array("tabindex" => "2"),
            "decorators" => $decorators,
            "validators" => array('NotEmpty', new Zend_Validate_Int())
        ));
        
        $this->addElement("select", "idProveedor", array(
            "class" => "traffic-select-large",
            "multiOptions" => array("" => "---"),
            "attribs" => array("tabindex" => "3", "disabled" => "disabled"),
            "decorators" => $decorators,
            "validators" => array('NotEmpty', new Zend_Validate_Int())
        ));
        
        $this->addElement("text", "proveedores", array(
            "class" => "traffic-input-large",
            "attribs" => array(
                "tabindex" => "3",
            ),
            "decorators" => $decorators,
        ));
        
        $this->addElement("select", "idPlanta", array(
            "class" => "traffic-select-medium",
            "multiOptions" => array("" => "---"),
            "attribs" => array("tabindex" => "4", "disabled" => "disabled"),
            "validators" => array(array('stringLength', array('min' => 7, 'max' => 8)))
        ));
        
        $this->addElement("text", "referencia", array(
            "class" => "traffic-input-small",
            "attribs" => array(
                "tabindex" => "6",
                "autocomplete" => "off"
            ),
            "decorators" => $decorators,
        ));
        
        $this->addElement("text", "blGuia", array(
            "class" => "traffic-input-medium",
            "attribs" => array(
                "tabindex" => "7",
                "autocomplete" => "off"
            ),
            "decorators" => $decorators,
        ));
        
        $this->addElement("text", "contenedorCaja", array(
            "class" => "traffic-input-medium",
            "attribs" => array(
                "tabindex" => "8",
                "autocomplete" => "off"
            ),
            "decorators" => $decorators,
        ));
        
        $this->addElement("text", "contenedorCajaEntrada", array(
            "class" => "traffic-input-medium",
            "attribs" => array(
                "tabindex" => "8",
                "autocomplete" => "off"
            ),
            "decorators" => $decorators,
        ));
        
        $this->addElement("select", "idLineaTransporte", array(
            "class" => "traffic-select-large",
            "multiOptions" => array("" => "---"),
            "attribs" => array("tabindex" => "9", "disabled" => "disabled"),
            "decorators" => $decorators,
            "validators" => array('NotEmpty', new Zend_Validate_Int())
        ));
        
        /*$this->addElement("text", "idLineaTransporte", array(
            "class" => "traffic-input-large",
            "attribs" => array(
                "tabindex" => "9",
            ),
            "decorators" => $decorators,
        ));*/
        
        $this->addElement("text", "bultos", array(
            "class" => "traffic-input-small",
            "attribs" => array(
                "tabindex" => "10",
                "autocomplete" => "off"
            ),
            "decorators" => $decorators,
        ));

        $this->addElement("text", "fechaEta", array(
            "class" => "traffic-input-date",
            "attribs" => array(
                "tabindex" => "11",
                "autocomplete" => "off"
            ),
            "decorators" => $decorators,
        ));

    }

}
