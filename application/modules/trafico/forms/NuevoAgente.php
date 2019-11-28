<?php

class Trafico_Form_NuevoAgente extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {

        $decorators = array("ViewHelper", "Errors", "Label",);

        $this->addElement("hidden", "id", array(
            "decorators" => $decorators,
        ));

        $this->addElement("text", "patente", array(
            "class" => "traffic-input-small",
            "attribs" => array("tabindex" => "1"),
            "decorators" => $decorators
        ));

        $this->addElement("text", "rfc", array(
            "class" => "traffic-input-medium",
            "attribs" => array("tabindex" => "2"),
            "decorators" => $decorators
        ));

        $this->addElement("text", "nombre", array(
            "class" => "traffic-input-large",
            "attribs" => array("tabindex" => "3"),
            "decorators" => $decorators
        ));
    }

}
