<?php

class Trafico_Form_CrearSolicitud extends Twitter_Bootstrap_Form_Horizontal {

    protected $_clientes;
    protected $_aduanas;
    protected $_operacion;

    public function setClientes($clientes = null) {
        $this->_clientes = $clientes;
    }
    
    public function setAduanas($aduanas = null) {
        $this->_aduanas = $aduanas;
    }
    
    public function setOperacion($operacion = null) {
        $this->_operacion = $operacion;
    }

    public function init() {
        
        $decorators = array ("ViewHelper","Errors", "Label");
        
        $this->addElement("select", "aduana", array(
            "class" => "traffic-select-large",
            "multiOptions" => isset($this->_aduanas) ? $this->_aduanas : array("" => "---"),
            "attribs" => array("tabindex" => "1"),
            "decorators"=> $decorators,
        ));
        
        $this->addElement("select", "cliente", array(
            "class" => "traffic-select-large",
            "multiOptions" => $this->_clientes,
            "attribs" => array("tabindex" => "2"),
            "decorators"=> $decorators,
        ));
        
        $this->addElement("select", "planta", array(
            "class" => "traffic-select-medium",
            "multiOptions" => array("" => "---"),
            "attribs" => array("disabled" => "disabled"),
        ));
        
        if(!isset($this->_operacion)) {
            $this->addElement("select", "operacion", array(
                "class" => "traffic-select-medium",
                "multiOptions" => array(
                    "" => "---",
                    "TOCE.IMP" => "Importación",
                    "TOCE.EXP" => "Exportación",
                ),
                "attribs" => array("tabindex" => "3"),
            ));
        } else {
            $this->addElement("select", "operacion", array(
                "class" => "traffic-select-medium",
                "multiOptions" => $this->_operacion,
                "attribs" => array("tabindex" => "3"),
            ));            
        }
        
        $this->addElement("text", "pedimento", array(
            "class" => "traffic-input-small",
            "attribs" => array("tabindex" => "4"),
            "decorators"=> $decorators,
        ));
        
        $this->addElement("text", "referencia", array(
            "class" => "traffic-input-small",
            "attribs" => array("tabindex" => "5"),
            "decorators"=> $decorators,
        ));
    }

}
