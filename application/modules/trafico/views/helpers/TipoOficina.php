<?php

class Zend_View_Helper_TipoOficina extends Zend_View_Helper_Abstract {

    public function tipoOficina($value) {
        $tbl = new Trafico_Model_TraficoTipoAduanaMapper();
        $tipo = $tbl->tipoAduana($value);
        return $tipo["tipoAduana"];
    }

}
