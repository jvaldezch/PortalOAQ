<?php

class Zend_View_Helper_TableRow extends Zend_View_Helper_Abstract {

    public function tableRow($estatus) {
        if ($estatus == 'CRUZO') {
            return ' class="info"';
        } elseif($estatus == 'PENDIENTE DE RECIBIR ANTICIPO') {
            return ' class="success"';
        } else {
            return '';
        }
    }

}
