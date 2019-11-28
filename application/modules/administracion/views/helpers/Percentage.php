<?php

class Zend_View_Helper_Percentage extends Zend_View_Helper_Abstract {

    public function percentage($value) {
        return number_format($value, 2, '.', ',') . '%';
    }

}
