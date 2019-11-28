<?php

class Zend_View_Helper_TipoEdoc extends Zend_View_Helper_Abstract {

    public function tipoEdoc($id) {
        $vucemDoc = new Archivo_Model_DocumentosMapper();
        $tipo = $vucemDoc->tipoDocumento($id);
        if (preg_match('/Digitalización VUCEM/i', $tipo) && $id == 170) {
            return "Factura";
        }
        return $tipo;
    }

}
