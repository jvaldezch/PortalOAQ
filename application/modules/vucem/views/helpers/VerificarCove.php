<?php

class Zend_View_Helper_VerificarCove extends Zend_View_Helper_Abstract {

    protected $_config;

    public function verificarCove($uuid) {
        $solicitud = new Vucem_Model_VucemSolicitudesMapper();
        $cove = $solicitud->obtenerCove($uuid);

        if (!$cove) {
            return null;
        } else {
            return true;
        }
    }

}
