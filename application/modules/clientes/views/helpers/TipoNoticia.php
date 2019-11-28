<?php

class Zend_View_Helper_TipoNoticia extends Zend_View_Helper_Abstract {

    public function tipoNoticia($clientes, $interno, $publico) {
        if ($clientes == 1) {
            return "Clientes";
        } elseif ($interno == 1) {
            return "Interno";
        } elseif ($publico == 1) {
            return "Público";
        } else {
            return "N/d";
        }
    }

}
