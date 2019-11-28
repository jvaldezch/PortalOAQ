<?php

class Comercializacion_Form_DatosCliente extends Twitter_Bootstrap_Form_Horizontal {

    protected $_id = null;
    protected $_internal = null;

    protected function setId($id) {
        $this->_id = $id;
    }

    protected function setInternal($internal) {
        $this->_internal = $internal;
    }

    public function init() {
        
        $decorators = array("ViewHelper", "Errors", "Label",);
        
        $this->addElement("hidden", "id", array("decorators" => $decorators));
        $this->addElement("hidden", "nombre", array("decorators" => $decorators));
        
        $this->addElement("text", "rfc", array(
            "class" => "traffic-input-medium",
        ));

        $this->addElement("text", "nombre", array(
            "class" => "traffic-input-small",
            "attribs" => array("autocomplete" => "off"),
        ));

        $this->addElement("text", "sicaId", array(
            "class" => "traffic-input-small",
            "attribs" => array("autocomplete" => "off"),
        ));

        $this->addElement("checkbox", "webaccess", array(
            "class" => "traffic-input-small",
        ));

        $this->addElement("text", "password", array(
            "class" => "traffic-input-small",
            "attribs" => array("autocomplete" => "off"),
        ));
        
        $this->addElement("text", "dashboard", array(
            "class" => "traffic-input-small",
            "attribs" => array("autocomplete" => "off"),
        ));
    }

}
