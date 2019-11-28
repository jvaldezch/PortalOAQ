<?php

class Usuarios_Form_AsignarFiel extends Twitter_Bootstrap_Form_Horizontal {

    protected $_id = null;

    protected function setId($id) {
        $this->_id = $id;
    }

    public function init() {

        $model = new Vucem_Model_VucemFirmanteMapper();
        $sellos = $model->obtenerSellosDisponibles();
        $data[""] = "---";
        foreach ($sellos as $item) {
            $data[$item["rfc"]] = $item["razon"];
        }

        $this->setIsArray(true);

        $this->addElement("select", "razonSocial", array(
            "class" => "traffic-select-large",
            "required" => true,
            "decorators" => array("ViewHelper", "Errors", "Label",),
            "multiOptions" => $data,
        ));

        $this->addElement("select", "patenteFiel", array(
            "class" => "traffic-select-small",
            "multiOptions" => array(
                "" => "---"
            ),
            "attribs" => array("disabled" => "disabled"),
            "decorators" => array("ViewHelper", "Errors", "Label",)
        ));

        $this->addElement("select", "aduanaFiel", array(
            "class" => "traffic-select-small",
            "multiOptions" => array(
                "" => "---"
            ),
            "attribs" => array("disabled" => "disabled"),
            "decorators" => array("ViewHelper", "Errors", "Label",)
        ));
    }

}
