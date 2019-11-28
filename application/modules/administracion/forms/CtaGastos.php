<?php

class Administracion_Form_CtaGastos extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $deco = array("ViewHelper", "Errors", "Label");
        
        $this->addElement("text", "rfc", array(
            "placeholder" => "RFC del cliente",
            "class" => "traffic-input-small",
            "decorators" => $deco
        ));

        $this->addElement("text", "nombre", array(
            "placeholder" => "Nombre del cliente",
            "class" => "traffic-input-large",
            "attribs" => array("autocomplete" => "off"),
            "decorators" => $deco
        ));

        $this->addElement("text", "fechaIni", array(
            "placeholder" => "Fecha de inicio",
            "class" => "traffic-input-date",
            "decorators" => $deco
        ));

        $this->addElement("text", "fechaFin", array(
            "placeholder" => "Fecha fin",
            "class" => "traffic-input-date",
            "decorators" => $deco
        ));

        $this->addElement("checkbox", "desglose", array(
            "class" => "focused",
            "decorators" => $deco
        ));

    }

}
