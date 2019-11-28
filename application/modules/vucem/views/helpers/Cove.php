<?php

class Zend_View_Helper_Cove extends Zend_View_Helper_Abstract {

    protected $_config;

    public function cove($uuid) {
        $solicitud = new Vucem_Model_VucemSolicitudesMapper();
        $cove = $solicitud->obtenerCove($uuid);

        if (!$cove) {
            return "&nbsp;";
        } else {
            return $cove['cove'];
        }
    }

}
