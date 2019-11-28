<?php

class Trafico_Form_EditarOficina extends Twitter_Bootstrap_Form_Horizontal {

    protected $_idAduana = null;

    public function setIdAduana($idAduana = null) {
        $this->_idAduana = $idAduana;
    }

    public function init() {
        $deco = array("ViewHelper", "Errors", "Label",);

        $this->addElement("hidden", "idAduana", array(
            "decorators" => $deco,
            "value" => $this->_idAduana
        ));

        $this->addElement("text", "nombre", array(
            "label" => "NOMBRE:",
            "class" => "traffic-input-medium",
            "attribs" => array("tabindex" => "1"),
            "decorators" => $deco,
        ));

        $this->addElement("text", "email", array(
            "label" => "EMAIL:",
            "class" => "traffic-input-large",
            "attribs" => array("tabindex" => "2"),
            "decorators" => $deco,
        ));

        $tcon = new Trafico_Model_TipoContactoMapper();
        $rows = $tcon->obtenerTodos();
        $options = array("" => "---");
        if (isset($rows) && !empty($rows)) {
            foreach ($rows as $item) {
                $options[$item["id"]] = $item["tipo"];
            }
        }

        $this->addElement("select", "tipoContacto", array(
            "label" => "DEPTO:",
            "class" => "traffic-input-large",
            "attribs" => array("tabindex" => "3"),
            "multioptions" => $options,
            "decorators" => $deco,
        ));

        $mapper = new Trafico_Model_ClientesMapper();
        $arr = $mapper->obtener();
        $options = array("" => "---");
        if (isset($arr) && !empty($arr)) {
            foreach ($arr as $item) {
                $options[$item["id"]] = $item["nombre"];
            }
        }
        $this->addElement("select", "idCliente", array(
            "label" => "CLIENTE:",
            "class" => "traffic-select-large",
            "attribs" => array("tabindex" => "10"),
            "multioptions" => $options,
            "decorators" => $deco,
        ));

        $this->addElement("text", "nombreAlmacen", array(
            "label" => "NOMBRE DE ALMACEN:",
            "class" => "traffic-input-large",
            "attribs" => array("tabindex" => "20"),
            "decorators" => $deco,
        ));

        $this->addElement("text", "nombreTransporte", array(
            "label" => "NOMBRE DE TRANSPORTE:",
            "class" => "traffic-input-large",
            "attribs" => array("tabindex" => "30"),
            "decorators" => $deco,
        ));

        $this->addElement("text", "nombreNaviera", array(
            "label" => "NOMBRE DE NAVIERA:",
            "class" => "traffic-input-large",
            "attribs" => array("tabindex" => "40"),
            "decorators" => $deco,
        ));

        $model = new Trafico_Model_TipoConceptoMapper();
        $rows = $model->obtener();
        $options = array("" => "---");
        if (isset($rows) && !empty($rows)) {
            foreach ($rows as $item) {
                $options[$item["id"]] = strtoupper($item["tipoConcepto"]);
            }
        }
        $this->addElement("select", "idTipoConcepto", array(
            "label" => "TIPO DE CONCEPTO:",
            "class" => "traffic-select-large",
            "attribs" => array("tabindex" => "50"),
            "multioptions" => $options,
            "decorators" => $deco,
        ));
    }

}
