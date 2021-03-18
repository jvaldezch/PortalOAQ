<?php

class Trafico_Form_NuevoContactoCli extends Twitter_Bootstrap_Form_Horizontal {

    protected $_idCliente = null;

    public function setIdCliente($idCliente = null) {
        $this->_idCliente = $idCliente;
    }

    public function init() {
        $decorators = array("ViewHelper", "Errors", "Label",);

        $tbl = new Trafico_Model_TipoContactoMapper();
        $types = $tbl->obtenerTodos();
        $tipos[""] = "---";
        foreach ($types as $tipo) {
            $tipos[$tipo["id"]] = $tipo["tipo"];
        }

        $this->addElement("hidden", "idCliente", array(
            "value" => $this->_idCliente
        ));

        $this->addElement("text", "nombre", array(
            "placeholder" => "Nombre de contacto",
            "class" => "traffic-input-large",
            "autocomplete" => "off",
            "decorators" => $decorators
        ));

        $this->addElement("text", "email", array(
            "placeholder" => "Email de contacto",
            "class" => "traffic-input-large",
            "autocomplete" => "off",
            "decorators" => $decorators
        ));

        $this->addElement("select", "tipoContacto", array(
            "class" => "traffic-select-large",
            "decorators" => $decorators,
            "multiOptions" => $tipos,
        ));
        
        $mppr = new Trafico_Model_ClientesPlantas();
        $plantas = $mppr->obtener($this->_idCliente);
        
        $p[""] = "---";
        
        foreach ($plantas as $planta) {
            $p[$planta["id"]] = $planta["ubicacion"] . " " . $planta["descripcion"];
        }
        
        if (!empty($plantas)) {
            $this->addElement("select", "idPlanta", array(
                "class" => "traffic-select-large",
                "decorators" => $decorators,
                "multiOptions" => $p,
            ));
        }
    }

}
