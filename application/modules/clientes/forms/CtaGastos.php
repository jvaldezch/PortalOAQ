<?php

class Clientes_Form_CtaGastos extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {        
        $deco = array('ViewHelper', 'Errors', 'Label',);

        $this->addElement("text", "rfc", array(
            "placeholder" => "RFC del cliente",
            "class" => "traffic-input-medium traffic-input-readonly",
            "attribs" => array("readonly" => "true"),
            "decorators" => $deco,
        ));

        $this->addElement("text", "fechaIni", array(
            "placeholder" => "Fecha de inicio",
            "class" => "traffic-input-date",
            "decorators" => $deco,
        ));

        $this->addElement("text", "fechaFin", array(
            "placeholder" => "Fecha fin",
            "class" => "traffic-input-date",
            "decorators" => $deco,
        ));

    }

}
