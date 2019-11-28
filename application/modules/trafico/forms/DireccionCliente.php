<?php

class Trafico_Form_DireccionCliente extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $this->setIsArray(true);
        $this->setAttrib("id", "formAddress");
        $this->setAttrib("class", "traffic-form");

        $this->setDecorators(array(
            "FormElements",
            array("HtmlTag", array("tag" => "table", "class" => "traffic-table traffic-table-left")),
            "Form"
        ));
        
        $this->setElementDecorators(array(
            "ViewHelper",
            "Errors",
            array(array("data" => "HtmlTag"), array("tag" => "td")),
            array("Label", array("tag" => "td")),
            array(array("row" => "HtmlTag"), array("tag" => "tr"))
        ));

        $tbl = new Trafico_Model_TipoContactoMapper();
        $types = $tbl->obtenerTodos();
        $tipos[""] = "---";
        foreach ($types as $tipo) {
            $tipos[$tipo["id"]] = $tipo["tipo"];
        }

        $this->addElement("hidden", "id", array());
        $this->addElement("hidden", "idCliente", array());
        $this->addElement("hidden", "cvecte", array());
        $this->addElement("hidden", "rfcCliente", array());

        $this->addElement("text", "razon_soc", array(
            "label" => "Razón social:",
            "class" => "traffic-input-large",
        ));

        $this->addElement("text", "calle", array(
            "label" => "Calle:",
            "class" => "traffic-input-large",
        ));

        $this->addElement("text", "numext", array(
            "label" => "Num. Exterior:",
            "class" => "traffic-input-medium",
        ));

        $this->addElement("text", "numint", array(
            "label" => "Num. interior:",
            "class" => "traffic-input-medium",
        ));

        $this->addElement("text", "colonia", array(
            "label" => "Colonia:",
            "class" => "traffic-input-large",
        ));

        $this->addElement("text", "localidad", array(
            "label" => "Localidad:",
            "class" => "traffic-input-large",
        ));

        $this->addElement("text", "municipio", array(
            "label" => "Municipio:",
            "class" => "traffic-input-large",
        ));

        $mapper = new Application_Model_InegiEstados();
        $arr = ["" => "---"];
        foreach($mapper->obtenerTodos() as $item) {
            $arr[$item["abrev"]] = mb_strtoupper($item["nombre"]);
        }
        $this->addElement("select", "estado", array(
            "label" => "Estado:",
            "class" => "traffic-select-large",
            "multiOptions" => $arr
        ));

        $this->addElement("text", "cp", array(
            "label" => "C.P.:",
            "class" => "traffic-input-small",
            "attribs" => array("style" => "width: 150px"),
        ));
        
        $mapper = new Vucem_Model_VucemPaisesMapper();
        $arr = ["" => "---"];
        foreach($mapper->getAllCountries() as $item) {
            $arr[$item["cve_pais"]] = $item["nombre"];
        }
        $this->addElement("select", "pais", array(
            "label" => "País:",
            "class" => "traffic-select-large",
            "multiOptions" => $arr
        ));
    }

}
