<?php

class Zend_View_Helper_Pdf extends Zend_View_Helper_Abstract {

    protected $_config;

    public function pdf($uuid) {
        $solicitud = new Vucem_Model_VucemSolicitudesMapper();
        $cove = $solicitud->obtenerCove($uuid);
        if (!$cove) {
            return "&nbsp;";
        } else {
            return '<a href="/vucem/index/ver-cove-pdf?uuid=' . $uuid . '&filename=' . $cove["cove"] . '"><img src="/images/icons/pdf-icon.png" /></a>';
        }
    }

}
