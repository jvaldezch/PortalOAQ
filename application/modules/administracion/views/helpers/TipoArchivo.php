<?php

class Zend_View_Helper_TipoArchivo extends Zend_View_Helper_Abstract {

    public function tipoArchivo($value) {
        $mapper = new Administracion_Model_DocumentosArchivosMapper();
        return $mapper->tipoArchivo($value);
    }

}
