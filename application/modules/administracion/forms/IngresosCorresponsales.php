<?php

class Administracion_Form_IngresosCorresponsales extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $this->setIsArray(true);
        $this->_addClassNames("well");

        $years = array();
        foreach (range(date("Y"), 2009, -1) as $number) {
            $years[$number] = $number;
        }
        $this->addElement("select", "year", array(
            "class" => "traffic-select-small",
            "multiOptions" => $years,
        ))->setDefault("year", date("Y"));
        
        $mapper = new Administracion_Model_CorresponsalesCuentas();
        $array =  $mapper->getAll();
        $data[""] = "---";
        foreach ($array as $item) {
            $data[$item["id"]] = $item["nombre"];
        }

        $this->addElement("select", "corresponsal", array(
            "class" => "traffic-select-large",
            "attribs" => array("autocomplete" => "off"),
            "multiOptions" => $data,
        ));
    }

}
