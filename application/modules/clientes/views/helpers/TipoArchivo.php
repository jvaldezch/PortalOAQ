<?php

class Zend_View_Helper_TipoArchivo extends Zend_View_Helper_Abstract {

    public function tipoArchivo($id) {
        $vucemDoc = new Archivo_Model_DocumentosMapper();
        return $vucemDoc->tipoDocumento($id);
    }

}
