<?php

class Comercializacion_Form_BuscarCliente extends Twitter_Bootstrap_Form_Vertical {

    public function init() {
        $this->setIsArray(true);
        $this->setElementsBelongTo("bootstrap");

        $this->_addClassNames("well");

        $this->setAction("/comercializacion/index/clientes");
        $this->setMethod("POST");

        $this->addElement("text", "rfc", array(
            "label" => "RFC",
            "placeholder" => "RFC",
            "class" => "focused"
        ));

        $this->addElement("text", "nombre", array(
            "label" => "Nombre",
            "placeholder" => "Nombre",
            "class" => "focused",
            "attribs" => array("autocomplete" => "off", "style" => "width: 350px"),
        ));

        $this->addElement("button", "submit", array(
            "label" => "Buscar",
            "type" => "submit",
            "buttonType" => "primary",
            "decorators" => Array("ViewHelper", "HtmlTag"),
            "attribs" => array("style" => "margin-top:5px"),
        ));
    }

}
