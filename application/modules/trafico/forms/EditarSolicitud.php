<?php

class Trafico_Form_EditarSolicitud extends Twitter_Bootstrap_Form_Horizontal {

    protected $_idAduana = null;

    protected function setIdAduana($idAduana = null) {
        $this->_idAduana = $idAduana;
    }

    public function init() {

        $this->setAttrib("id", "editar-soilicitud");
        $this->setMethod("POST");

        $banks = new Trafico_Model_TraficoBancosMapper();
        $accounts = $banks->obtenerTodos($this->_idAduana);
        $bancos = array();
        $bancos[] = "---";
        if (isset($accounts) && !empty($accounts)) {
            foreach ($accounts as $bank) {
                $bancos[$bank["id"]] = $bank["nombre"] . ", " . $bank["cuenta"];
            }
        }

        $deco = array("ViewHelper", "Errors", "Label",);

        $this->addElement("select", "banco", array(
            "attribs" => array("tabindex" => "100", "style" => "width: 250px"),
            "decorators" => $deco,
            "multioptions" => $bancos
        ));

        $mdt = new Trafico_Model_AlmacenMapper();
        $storages = $mdt->obtener($this->_idAduana);
        $almacenes = array();
        $almacenes[] = "---";
        if (isset($storages) && !empty($storages)) {
            foreach ($storages as $storage) {
                $almacenes[$storage["id"]] = $storage["nombre"];
            }
        }

        $this->addElement("select", "almacen", array(
            "attribs" => array("tabindex" => "101", "style" => "width: 250px"),
            "decorators" => $deco,
            "multioptions" => $almacenes
        ));
    }

}
