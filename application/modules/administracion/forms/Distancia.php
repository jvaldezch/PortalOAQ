<?php

class Administracion_Form_Distancia extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $mapper = new Application_Model_InegiEstados();
        $arr = ["" => "---"];
        foreach ($mapper->obtenerTodos() as $item) {
            $arr[$item["id"]] = mb_strtoupper($item["nombre"]);
        }
        $this->addElement("select", "estado", array(
            "class" => "traffic-select-large",
            "multiOptions" => $arr
        ));

        $this->addElement("select", "municipio", array(
            "class" => "traffic-select-large",
            "multiOptions" => ["" => "---"],
            "attribs" => array("disabled" => "disabled"),
        ));

        $this->addElement("select", "localidad", array(
            "class" => "traffic-select-large",
            "multiOptions" => ["" => "---"],
            "attribs" => array("disabled" => "disabled"),
        ));
    }

}
