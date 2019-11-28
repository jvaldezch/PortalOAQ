<?php

class Trafico_Form_BuscarSolicitud extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {

        $tbl = new Trafico_Model_CvePedimentos();
        $rows = $tbl->obtener();
        if (isset($rows) && !empty($rows)) {
            $data = array();
            $data[""] = "---";
            foreach ($rows as $item) {
                $data[$item["clave"]] = $item["clave"];
            }
        }
        
        $tbl = new Trafico_Model_ClientesMapper();
        $customers = $tbl->obtenerTodos();
        

        $this->setAttrib("id", "editar-soilicitud");
        $this->setMethod("post");

        $decorators = array("ViewHelper", "Errors", "Label",);

        $this->addElement("text", "buscar", array(
            "class" => "traffic-input-large",
            "attribs" => array("tabindex" => "1"),
            "decorators" => $decorators
        ));
        
        $this->addElement("select", "idCliente", array(
            "class" => "traffic-select-large",
            "attribs" => array("tabindex" => "2"),
            "decorators" => $decorators,
            "multioptions" => $customers,
        ));

        $this->addElement("text", "referencia", array(
            "class" => "traffic-input-small",
            "attribs" => array("tabindex" => "3"),
            "decorators" => $decorators
        ));

        $this->addElement("text", "pedimento", array(
            "class" => "traffic-input-small",
            "attribs" => array("tabindex" => "100"),
            "decorators" => $decorators
        ));

        $this->addElement("select", "cvePedimento", array(
            "class" => "traffic-select-small",
            "attribs" => array("tabindex" => "4"),
            "decorators" => $decorators,
            "multioptions" => $data,
        ));
    }

}
