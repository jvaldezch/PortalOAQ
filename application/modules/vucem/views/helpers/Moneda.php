<?php

class Zend_View_Helper_Moneda extends Zend_View_Helper_Abstract {

    public function moneda($value) {
        $misc = new OAQ_Misc();
        return $misc->tipoMoneda($value);
    }

}
