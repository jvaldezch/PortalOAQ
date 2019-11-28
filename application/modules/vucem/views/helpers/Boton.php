<?php

class Zend_View_Helper_Boton extends Zend_View_Helper_Abstract {

    protected $_config;

    public function boton($uuid) {
        $solicitud = new Vucem_Model_VucemSolicitudesMapper();
        $estatus = $solicitud->verificarEstatus($uuid);

        if (!$estatus) {
            return false;
        }
        return true;
    }

}
