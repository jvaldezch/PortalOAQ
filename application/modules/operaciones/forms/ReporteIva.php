<?php

class Operaciones_Form_ReporteIva extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $this->setIsArray(true);
        $this->setMethod("GET");
        $this->setAttrib("id", "form-report");

        $this->addElement("select", "aduana", array(
            "required" => true,
            "class" => "traffic-input-medium",
            "multiOptions" => array(
                1 => "3589-640 - Querétaro",
                2 => "3589-240 - Nuevo Laredo",
                3 => "3589-800 - Colombia, Nuevo León",
            ),
        ));
        
        $this->addElement("text", "rfc_cliente", array(
            "required" => true,
            "class" => "traffic-input-medium"
        ));

        $this->addElement("text", "nombre", array(
            "required" => true,
            "class" => "traffic-input-large",
            "attribs" => array("autocomplete" => "off"),
        ));

        $years = array();
        foreach (range(date("Y"), 2012, -1) as $number) {
            $years[$number] = $number;
        }
        $this->addElement("select", "year", array(
            "multiOptions" => $years,
            "value" => date("Y"),
            "class" => "traffic-select-small"
        ));

        $this->addElement("select", "mes", array(
            "multiOptions" => array(
                "1" => "Enero",
                "2" => "Febrero",
                "3" => "Marzo",
                "4" => "Abril",
                "5" => "Mayo",
                "6" => "Junio",
                "7" => "Julio",
                "8" => "Agosto",
                "9" => "Septiembre",
                "10" => "Octubre",
                "11" => "Noviembre",
                "12" => "Diciembre",
            ),
            "value" => date("m"),
            "class" => "traffic-select-small"
        ));
    }

}
