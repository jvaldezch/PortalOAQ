<?php

class V2_Form_Trafico_NuevoTrafico extends Zend_Form {

    protected $idUsuario;

    function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    public function init() {

        if(isset($this->idUsuario)) {
            $mapper = new V2_Model_Trafico_UsuarioAduanas();
            $aduanas = $mapper->obtenerAduanas($this->idUsuario);
        }
        
        $tbl = new Trafico_Model_CvePedimentos();
        $rows = $tbl->obtener();
        if (isset($rows) && !empty($rows)) {
            $data = array();
            $data[""] = "---";
            foreach ($rows as $item) {
                $data[$item["clave"]] = $item["clave"];
            }
        }

        $this->setDecorators(array(
            "FormElements",
            array("HtmlTag", array("tag" => "table")),
            "Form"
        ));

        $this->setElementDecorators(array(
            "ViewHelper",
            "Errors",
            array(array("data" => "HtmlTag"), array("tag" => "td")),
            array("Label", array("tag" => "td")),
            array(array("row" => "HtmlTag"), array("tag" => "tr"))
        ));
        
        $this->addElement("hidden", "idUsuario", array(
            "decorators" => array("ViewHelper"),
            "value" => isset($this->idUsuario) ? $this->idUsuario : ""
        ));

        $this->addElement("select", "idCliente", array(
            "label" => "Cliente:",
            "multiOptions" => array(
                "" => "---"
            ),
            "class" => "large",
        ));

        $this->addElement("select", "idAduana", array(
            "label" => "Aduana:",
            "placeholder" => "Aduana",
            "multiOptions" => isset($aduanas) ? $aduanas : array("" => "---"),
            "class" => "medium",
        ));
        
        $this->addElement("select", "tipoOperacion", array(
            "label" => "Tipo Op.:",
            "multiOptions" => array(
                "" => "---",
                "1" => "IMPO",
                "2" => "EXPO",
            ),
            "class" => "small",
        ));

        $this->addElement("select", "cvePedimento", array(
            "label" => "Cve. Pedimento:",
            "placeholder" => "Cve. Pedimento",
            "multioptions" => $data,
            "class" => "small",
        ));

        $this->addElement("text", "referencia", array(
            "label" => "Referencia:",
            "placeholder" => "Referencia",
        ));

        $this->addElement("text", "pedimento", array(
            "label" => "Pedimento:",
            "placeholder" => "Pedimento",
        ));

        $this->addElement("button", "submit", array(
            "label" => "Crear tráfico",
            "type" => "submit",
            "class" => "button-blue",
            "decorators" => Array("ViewHelper", "HtmlTag"),
        ));
    }

}
