<?php

class Operaciones_Form_Validador extends Twitter_Bootstrap_Form_Horizontal {

    protected $_usuario;

    public function setUsuario($usuario = null) {
        $this->_usuario = $usuario;
    }

    public function init() {

        $model = new Trafico_Model_TraficoUsuAduanasValMapper();
        $patentes = $model->obtenerPatentes($this->_usuario);

        $this->addElement("select", "patente", array(
            "class" => "focused",
            "multiOptions" => $patentes,
            "attribs" => array("style" => "width: 80px", "tabindex" => "1"),
            "decorators" => array("ViewHelper", "Errors", "Label",),
        ));

        $this->addElement("select", "aduana", array(
            "multiOptions" => array("" => "---"),
            "attribs" => array("style" => "width: 80px", "tabindex" => "2", "disabled" => "disabled"),
        ));

        $this->addElement("text", "pedimento", array(
            "attribs" => array("style" => "width: 80px", "tabindex" => "3"),
        ));
    }

}
