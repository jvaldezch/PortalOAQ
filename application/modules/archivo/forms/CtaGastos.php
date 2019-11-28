<?php

class Archivo_Form_CtaGastos extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        
        $decorators = array("ViewHelper", "Errors", "Label");

        $this->addElement("text", "rfc", array(
            "label" => "RFC del cliente:",
            "placeholder" => "RFC del cliente",
            "class" => "traffic-input-medium",
            "decorators" => $decorators,
        ));

        $this->addElement("text", "nombre", array(
            "label" => "Nombre del cliente:",
            "placeholder" => "Nombre del cliente",
            "class" => "traffic-input-large",
            "attribs" => array("autocomplete" => "off"),
            "decorators" => $decorators,
        ));

        $this->addElement("text", "fechaIni", array(
            "label" => "Fecha inicio:",
            "class" => "traffic-input-date",
            "attribs" => array("name" => "fechaIni"),
            "decorators" => $decorators,
        ));

        $this->addElement("text", "fechaFin", array(
            "label" => "Fecha fin:",
            "class" => "traffic-input-date",
            "attribs" => array("name" => "fechaFin"),
            "decorators" => $decorators,
        ));

        $this->addElement("button", "submit", array(
            "label" => "Buscar",
            "type" => "submit",
            "buttonType" => "primary",
            "decorators" => Array("ViewHelper", "HtmlTag"),
        ));
    }

}
