<?php

class Usuarios_Form_Aduanas extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {

        $patente = array("" => "---");
        $adus = new Trafico_Model_TraficoAduanasMapper();
        foreach ($adus->obtenerPatentes() as $item) {
            $patente[$item["patente"]] = $item["patente"];
        }

        $this->setIsArray(true);
        $this->addElement("select", "patentesExpediente", array(
            "class" => "traffic-select-small",
            "multiOptions" => $patente,
            "decorators" => array("ViewHelper", "Errors", "Label",)
        ));

        $this->addElement("select", "aduanasExpediente", array(
            "class" => "traffic-select-large",
            "multiOptions" => array(
                "" => "---"
            ),
            "attribs" => array("disabled" => "disabled"),
            "decorators" => array("ViewHelper", "Errors", "Label",)
        ));
    }

}
