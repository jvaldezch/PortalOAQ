<?php

class Trafico_Form_EditarCliente extends Twitter_Bootstrap_Form_Horizontal {

    protected $_idCliente = null;

    public function setIdAduana($idCliente = null) {
        $this->_idCliente = $idCliente;
    }

    public function init() {
        $deco = array("ViewHelper", "Errors", "Label",);

        $this->addElement("hidden", "idCliente", array(
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

    }

}
