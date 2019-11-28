<?php

class Administracion_Form_EnvioCorresponsales extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $deco = array("ViewHelper", "Errors", "Label",);
        
        $this->addElement("text", "fechaIni", array(
            "placeholder" => "Fecha de corte",
            "class" => "traffic-input-date",
            "decorators" => $deco
        ));

        $this->addElement("radio", "opcion", array(
            "multiOptions" => array(
                0 => "Todo",
                1 => "Por comprobar",
                2 => "Saldos",
            ),
            "value" => 0,
            "decorators" => $deco
        ));
        
    }

}
