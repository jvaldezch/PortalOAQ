<?php

class Trafico_Form_NuevoCliente extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $decorators = array("ViewHelper", "Errors", "Label",);

        $this->addElement("hidden", "id", array(
            "decorators" => $decorators,
        ));

        $this->addElement("text", "rfc", array(
            "class" => "traffic-input-medium",
            "attribs" => array("tabindex" => "1"),
            "decorators" => $decorators,
        ));

        $this->addElement("textarea", "nombre", array(
            "class" => "traffic-textarea-medium",
            "attribs" => array("tabindex" => "2"),
            "decorators" => $decorators,
        ));
        
        $this->addElement("text", "rfcSociedad", array(
            "class" => "traffic-input-medium",
            "attribs" => array("tabindex" => "3"),
            "decorators" => $decorators,
        ));
    }

}
