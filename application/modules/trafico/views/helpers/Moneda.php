<?php

class Zend_View_Helper_Moneda extends Zend_View_Helper_Abstract {

    public function moneda($value) {
        return "$ " . number_format($value, 4, '.', ',');
    }

}
