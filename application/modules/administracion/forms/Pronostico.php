<?php

class Administracion_Form_Pronostico extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $decorators = array("ViewHelper", "Errors", "Label");

        $this->addElement("text", "rfc", array(
            "placeholder" => "RFC del cliente",
            "class" => "traffic-input-medium",
            "decorators" => $decorators,
        ));

        $this->addElement("text", "nombre", array(
            "placeholder" => "Nombre del cliente",
            "class" => "traffic-input-large",
            "attribs" => array("autocomplete" => "off"),
            "decorators" => $decorators,
        ));

        $this->addElement("checkbox", "sum", array(
            "class" => "focused",
            "decorators" => $decorators,
        ));

        $this->addElement("checkbox", "desglose", array(
            "class" => "focused",
            "decorators" => $decorators,
        ));

        $this->addElement("text", "fechaIni", array(
            "placeholder" => "Fecha de corte",
            "class" => "traffic-input-date",
            "decorators" => $decorators,
        ));
    }

}
