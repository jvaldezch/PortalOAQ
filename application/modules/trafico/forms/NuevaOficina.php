<?php

class Trafico_Form_NuevaOficina extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {

        $decorators = array("ViewHelper", "Errors", "Label",);

        $this->addElement("hidden", "id", array(
            "decorators" => $decorators,
        ));
        
        $arr = array("" => "---");
        $catpat = new Trafico_Model_Agentes();
        foreach ($catpat->todos() as $item) {
            $arr[$item["patente"]] = $item["patente"] . " " . $item["nombre"];
        }

        $this->addElement("select", "patente", array(
            "class" => "traffic-select-large",
            "attribs" => array("tabindex" => "1"),
            "decorators" => $decorators,
            "multioptions" => $arr,
        ));

        $arr = array("" => "---");
        $catadu = new Trafico_Model_CatAduanas();
        foreach ($catadu->todas() as $item) {
            $arr[$item["clave"]] = $item["clave"] . " " . $item["nombre"];
        }
        
        $this->addElement("select", "aduana", array(
            "class" => "traffic-select-large",
            "attribs" => array("tabindex" => "2"),
            "decorators" => $decorators,
            "multioptions" => $arr,
        ));

        $mapper = new Trafico_Model_TraficoTipoAduanaMapper();
        $tipos = $mapper->obtenerTodas();
        $tipoAduana = array("" => "---");
        if (isset($tipos)) {
            foreach ($tipos as $item) {
                $tipoAduana[$item["id"]] = mb_strtoupper($item["tipoAduana"]);
            }
        }

        $this->addElement("select", "tipoAduana", array(
            "class" => "traffic-select-medium",
            "attribs" => array("tabindex" => "4"),
            "decorators" => $decorators,
            "multioptions" => $tipoAduana
        ));

        $this->addElement("select", "corresponsal", array(
            "class" => "traffic-select-medium",
            "attribs" => array("tabindex" => "4"),
            "decorators" => $decorators,
            "multioptions" => array(
                "" => "---",
                0 => "NO",
                1 => "SI",
            )
        ));        

        $this->addElement("text", "nombre", array(
            "class" => "traffic-input-large",
            "attribs" => array("tabindex" => "5"),
            "decorators" => $decorators
        ));

    }

}
