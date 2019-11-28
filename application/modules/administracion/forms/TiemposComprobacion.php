<?php

class Administracion_Form_TiemposComprobacion extends Twitter_Bootstrap_Form_Horizontal {

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

        $this->addElement("text", "fechaIni", array(
            "placeholder" => "Fecha de inicio",
            "class" => "traffic-input-date",
            "decorators" => $decorators,
        ));

        $this->addElement("text", "fechaFin", array(
            "placeholder" => "Fecha fin",
            "class" => "traffic-input-date",
            "decorators" => $decorators,
        ));
    }

}
