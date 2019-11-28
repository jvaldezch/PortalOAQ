<?php

class Zend_View_Helper_Solicitudes extends Zend_View_Helper_Abstract {

    protected $_config;

    public function solicitudes($uuid) {
        $solicitud = new Vucem_Model_VucemSolicitudesMapper();
        $estatus = $solicitud->verificarEstatus($uuid);

        if (!$estatus) {
            return "<div id=\"{$uuid}\" class=\"statusCove notsent\"></div>";
        } else {
            if ($estatus['estatus'] == 1) {
                return "<div data=\"{$estatus['solicitud']}\" id=\"{$uuid}\" class=\"statusCove sent\"></div>";
            }
            if ($estatus['estatus'] == 2) {
                return "<div data=\"{$estatus['solicitud']}\" id=\"{$uuid}\" class=\"statusCove cove\"></div>";
            }
            if ($estatus['estatus'] == 0) {
                return "<div data=\"{$estatus['solicitud']}\" id=\"{$uuid}\" class=\"statusCove error\"></div>";
            }
        }
    }

}
