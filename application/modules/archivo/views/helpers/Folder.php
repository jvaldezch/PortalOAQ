<?php

class Zend_View_Helper_Folder extends Zend_View_Helper_Abstract {

    public function folder($patente, $aduana, $referencia, $rol) {
        $roles = array("super", "administracion", "trafico", "trafico_operaciones");
        if (in_array($rol, $roles)) {
            return "<a href=\"/archivo/index/archivos-expediente?ref=" . urlencode($referencia) . "&patente=" . $patente . "&aduana=" . $aduana . "\"><div class=\"traffic-icon traffic-icon-folder\"></div></a>"
                    . "<a href=\"/archivo/index/modificar-referencia?ref=" . urlencode($referencia) . "&patente=" . $patente . "&aduana=" . $aduana . "\"><div class=\"traffic-icon traffic-icon-edit\"></div></a>";
        } else {
            return "<a href=\"/archivo/index/archivos-expediente?ref=" . urlencode($referencia) . "&patente=" . $patente . "&aduana=" . $aduana . "\"><div class=\"traffic-icon traffic-icon-folder\"></div></a>";
        }
    }

}
