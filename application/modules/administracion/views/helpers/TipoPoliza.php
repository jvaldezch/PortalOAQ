<?php

class Zend_View_Helper_TipoPoliza extends Zend_View_Helper_Abstract {

    public function tipoPoliza($value) {
        $mapper = new Administracion_Model_DocumentosPolizasMapper();
        return $mapper->tipoPoliza($value);                
    }

}
