<?php

class Usuarios_Form_Bodegas extends Twitter_Bootstrap_Form_Horizontal {

    protected $_bodegas;

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
    }

}
